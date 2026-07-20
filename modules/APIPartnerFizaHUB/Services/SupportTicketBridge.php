<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminSupport\Models\SupportComment;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportAttachment;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportPreset;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportTicketContext;
use Modules\APIPartnerFizaHUB\Support\PartnerApiException;
use Modules\AppQRCampaigns\Models\QrCampaign;
use RuntimeException;

class SupportTicketBridge
{
    public function __construct(
        protected PartnerMappingService $mapping
    ) {}

    public function findIntegrationOrFail(string $externalBusinessId): PartnerIntegration
    {
        $integration = PartnerIntegration::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->where('external_business_id', $this->mapping->normalizeExternalId($externalBusinessId))
            ->whereNotNull('mlhub_user_id')
            ->whereNotNull('mlhub_workspace_id')
            ->first();

        if (! $integration) {
            throw (new ModelNotFoundException)->setModel(PartnerIntegration::class, [$externalBusinessId]);
        }

        return $integration;
    }

    /**
     * @param  array{subject: string, message: string, category_id?: int|null, type_id?: int|null}  $input
     */
    public function create(PartnerIntegration $integration, array $input): SupportTicket
    {
        $message = $this->plainTextMessage((string) $input['message']);

        $ticket = SupportTicket::query()->create([
            'id_secure' => Str::random(32),
            'uid' => (int) $integration->mlhub_user_id,
            'open_by' => (int) $integration->mlhub_user_id,
            'team_id' => (int) $integration->mlhub_workspace_id,
            'cate_id' => $input['category_id'] ?? null,
            'type_id' => $input['type_id'] ?? null,
            'title' => trim((string) $input['subject']),
            'content' => $message,
            'status' => 1,
            'pin' => false,
            'user_read' => false,
            'admin_read' => true,
            'created' => time(),
            'changed' => time(),
        ]);

        $this->safeLog('partner.fizahub.support.create', 'Created a FizaHUB support ticket.', [
            'subject_type' => SupportTicket::class,
            'subject_id' => $ticket->id,
            'area' => 'admin',
            'metadata' => [
                'ticket' => $ticket->id_secure,
                'external_business_id' => $integration->external_business_id,
            ],
        ]);

        return $ticket;
    }

    /**
     * Create a ticket from a preset code or from legacy subject/message input.
     *
     * @param  array<string, mixed>  $input
     */
    public function createForBusiness(PartnerIntegration $integration, array $input): SupportTicket
    {
        $presetCode = isset($input['preset_code']) ? trim((string) $input['preset_code']) : '';
        $campaignId = isset($input['campaign_id']) ? trim((string) $input['campaign_id']) : '';
        $metadata = (array) ($input['metadata'] ?? []);
        $responseChannel = (string) ($input['response_channel'] ?? 'in_app');
        $subject = isset($input['subject']) ? trim((string) $input['subject']) : '';
        $message = isset($input['message']) ? (string) $input['message'] : '';
        $preset = null;

        if ($presetCode !== '') {
            $preset = $this->findPreset($presetCode);

            if (! $preset) {
                throw PartnerApiException::make(
                    'support_preset_not_found',
                    __('Yêu cầu hỗ trợ không hợp lệ hoặc đã ngừng sử dụng.'),
                    422,
                    ['preset_code' => [$presetCode]]
                );
            }

            $subject = (string) ($preset->default_subject ?: $preset->name);

            if ($presetCode === 'campaign_request' && $campaignId === '') {
                throw PartnerApiException::make(
                    'support_context_required',
                    'Yêu cầu điều chỉnh chiến dịch cần campaign_id.',
                    422,
                    ['campaign_id' => ['required']]
                );
            }
        }

        if ($subject === '' || trim($message) === '') {
            throw PartnerApiException::make(
                'support_ticket_invalid',
                __('Vui lòng cung cấp tiêu đề và nội dung, hoặc chọn một mẫu yêu cầu.'),
                422
            );
        }

        $ticket = $this->create($integration, [
            'subject' => $subject,
            'message' => $message,
            'category_id' => $preset?->category_id,
            'type_id' => $preset?->type_id,
        ]);

        $this->storeContext($integration, $ticket, $presetCode, $campaignId, $responseChannel, $metadata);

        return $ticket;
    }

    public function campaignRequestTicket(
        PartnerIntegration $integration,
        QrCampaign $campaign,
        ?string $note = null
    ): SupportTicket {
        $context = PartnerSupportTicketContext::query()
            ->where('partner_integration_id', $integration->id)
            ->where('request_code', 'campaign_request')
            ->where('related_resource_type', QrCampaign::class)
            ->where('related_resource_id', (string) $campaign->id)
            ->first();
        $existing = $context?->support_ticket_id
            ? SupportTicket::query()->find($context->support_ticket_id)
            : null;

        if ($existing) {
            return $existing;
        }

        $ticket = $this->create($integration, [
            'subject' => 'Yêu cầu điều chỉnh chiến dịch: '.$campaign->name,
            'message' => $note ?: 'Doanh nghiệp yêu cầu MLHUB rà soát và điều chỉnh chiến dịch.',
        ]);
        $this->storeContext(
            $integration,
            $ticket,
            'campaign_request',
            (string) $campaign->id,
            'in_app',
            ['source' => 'campaign_approval']
        );

        return $ticket;
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function list(PartnerIntegration $integration, array $filters = []): array
    {
        $base = $this->tenantTicketQuery($integration);
        $allTickets = (clone $base)->get(['status', 'user_read']);
        $query = clone $base;
        $search = trim((string) ($filters['q'] ?? ''));

        if ($search !== '') {
            $query->where(function ($inner) use ($search): void {
                $inner->where('id_secure', 'like', '%'.$search.'%')
                    ->orWhere('title', 'like', '%'.$search.'%')
                    ->orWhere('content', 'like', '%'.$search.'%')
                    ->orWhereHas('comments', fn ($comments) => $comments->where('comment', 'like', '%'.$search.'%'));
            });
        }

        $status = (string) ($filters['status'] ?? '');
        if ($status !== '') {
            $query->where('status', $this->databaseStatus($status));
        }

        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 20)));
        $paginator = $query
            ->orderByDesc('changed')
            ->orderByDesc('id')
            ->cursorPaginate($perPage);

        return [
            'summary' => [
                'open' => $allTickets->where('status', 1)->count(),
                'resolved' => $allTickets->where('status', 2)->count(),
                'closed' => $allTickets->where('status', 0)->count(),
                'unread_by_business' => $allTickets->where('user_read', false)->count(),
            ],
            'items' => collect($paginator->items())
                ->map(fn (SupportTicket $ticket): array => $this->serializeTicket($ticket))
                ->values()
                ->all(),
            'pagination' => [
                'next_cursor' => $paginator->nextCursor()?->encode(),
                'has_more' => $paginator->hasMorePages(),
                'per_page' => $paginator->perPage(),
            ],
        ];
    }

    /**
     * @param  list<array{type: string, id: int}>  $duplicates
     */
    public function createOnboardingReviewTicket(
        PartnerOnboardingRequest $onboarding,
        PartnerIntegration $integration,
        string $title,
        string $summary,
        array $duplicates = [],
        ?string $auditReason = null
    ): SupportTicket {
        // Onboarding always provisions a real MLHUB user/team before a review ticket is
        // ever created (see OnboardingService::provision()), so support_tickets.uid and
        // .open_by — which have a real FK to users.id in production — must reference that
        // provisioned account. Validate the mapping first instead of inserting uid=0/
        // open_by=0 and hoping a later update fixes it: that pattern violates the FK on
        // the very first insert (SQLSTATE 23000 / MySQL error 1452) and rolls back the
        // whole onboarding transaction, which is the exact production 500 this guards.
        [$userId, $teamId] = $this->resolveTicketOwnerOrFail($integration);

        $content = json_encode([
            'summary' => $summary,
            'request_id' => $onboarding->request_id,
            'external_business_id' => $onboarding->external_business_id,
            'package_code' => $onboarding->package_code,
            'status' => $onboarding->status,
            'verification_status' => $onboarding->verification_status,
            'duplicate_check' => $duplicates,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: $summary;

        $ticket = null;

        if ($onboarding->support_ticket_id) {
            $existing = SupportTicket::query()->find($onboarding->support_ticket_id);

            if ($existing) {
                $existing->forceFill([
                    'uid' => $userId,
                    'open_by' => $userId,
                    'team_id' => $teamId,
                    'title' => $title,
                    'content' => $content,
                    'changed' => time(),
                    'admin_read' => true,
                    'user_read' => false,
                ])->save();

                $ticket = $existing;
            }
        }

        if (! $ticket) {
            $ticket = SupportTicket::query()->create([
                'id_secure' => Str::random(32),
                'uid' => $userId,
                'open_by' => $userId,
                'team_id' => $teamId,
                'cate_id' => null,
                'type_id' => null,
                'title' => $title,
                'content' => $content,
                'status' => 1,
                'pin' => false,
                'user_read' => false,
                'admin_read' => true,
                'created' => time(),
                'changed' => time(),
            ]);
        }

        $this->storeOnboardingContext($integration, $ticket, $onboarding, $auditReason);

        return $ticket;
    }

    private function storeOnboardingContext(
        PartnerIntegration $integration,
        SupportTicket $ticket,
        PartnerOnboardingRequest $onboarding,
        ?string $auditReason
    ): void {
        if (! Schema::hasTable('partner_support_ticket_contexts')) {
            return;
        }

        PartnerSupportTicketContext::query()->updateOrCreate(
            ['support_ticket_id' => $ticket->id],
            [
                'partner_integration_id' => $integration->id,
                'external_business_id' => $integration->external_business_id,
                'request_code' => 'fizahub_onboarding',
                'package_code' => $integration->package_code,
                'related_resource_type' => PartnerOnboardingRequest::class,
                'related_resource_id' => (string) $onboarding->request_id,
                'context' => [
                    'ticket_type' => 'onboarding',
                    'source' => 'fizahub',
                    'preset_code' => 'fizahub_onboarding',
                    'audit_reason' => $auditReason,
                ],
            ]
        );
    }

    /**
     * @return array{0: int, 1: int|null}
     */
    private function resolveTicketOwnerOrFail(PartnerIntegration $integration): array
    {
        $userId = (int) ($integration->mlhub_user_id ?? 0);

        if ($userId <= 0 || ! User::query()->whereKey($userId)->exists()) {
            throw PartnerApiException::make(
                'integration_mapping_invalid',
                __('Liên kết tài khoản MLHUB chưa hoàn chỉnh.'),
                409,
                ['next_action' => 'retry_onboarding']
            );
        }

        $teamId = $integration->mlhub_workspace_id ? (int) $integration->mlhub_workspace_id : null;

        if ($teamId !== null && ! Team::query()->whereKey($teamId)->exists()) {
            throw PartnerApiException::make(
                'integration_mapping_invalid',
                __('Liên kết tài khoản MLHUB chưa hoàn chỉnh.'),
                409,
                ['next_action' => 'retry_onboarding']
            );
        }

        return [$userId, $teamId];
    }

    /**
     * @return array<string, mixed>
     */
    public function detail(
        PartnerIntegration $integration,
        string $ticketSecureId,
        ?CarbonImmutable $since = null
    ): array {
        $ticket = $this->findScopedTicketOrFail($integration, $ticketSecureId);
        $messages = $this->serializeMessages($ticket, $since);

        return array_merge($this->serializeTicket($ticket), [
            'messages' => $messages,
            'next_poll_after_seconds' => 15,
            'last_message_at' => $this->lastMessageAt($ticket),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function addMessage(
        PartnerIntegration $integration,
        string $ticketSecureId,
        string $message
    ): array {
        $ticket = $this->findScopedTicketOrFail($integration, $ticketSecureId);

        if (in_array((int) $ticket->status, [0, 2], true)) {
            throw new RuntimeException('ticket_not_open');
        }

        $plain = $this->plainTextMessage($message);

        $comment = SupportComment::query()->create([
            'id_secure' => Str::random(32),
            'ticket_id' => $ticket->id,
            'user_id' => (int) $integration->mlhub_user_id,
            'comment' => $plain,
            'created' => time(),
            'changed' => time(),
        ]);

        $ticket->forceFill([
            'admin_read' => true,
            'user_read' => false,
            'changed' => time(),
        ])->save();

        $this->safeLog('partner.fizahub.support.reply', 'Replied to a support ticket from FizaHUB.', [
            'subject_type' => SupportTicket::class,
            'subject_id' => $ticket->id,
            'area' => 'admin',
            'causer_user_id' => (int) $integration->mlhub_user_id,
            'metadata' => [
                'ticket' => $ticket->id_secure,
                'message_id' => $comment->id_secure,
                'external_business_id' => $integration->external_business_id,
            ],
        ]);

        return $this->serializeComment($ticket, $comment);
    }

    public function findScopedTicketOrFail(
        PartnerIntegration $integration,
        string $ticketSecureId
    ): SupportTicket {
        $ticket = SupportTicket::query()
            ->where('id_secure', $ticketSecureId)
            ->where('uid', (int) $integration->mlhub_user_id)
            ->when(
                $integration->mlhub_workspace_id,
                function ($query) use ($integration): void {
                    $query->where(function ($inner) use ($integration): void {
                        $inner->where('team_id', (int) $integration->mlhub_workspace_id)
                            ->orWhereNull('team_id');
                    });
                }
            )
            ->first();

        if (! $ticket) {
            throw (new ModelNotFoundException)->setModel(SupportTicket::class, [$ticketSecureId]);
        }

        return $ticket;
    }

    public function resolveIntegrationForTicket(string $ticketSecureId): PartnerIntegration
    {
        $ticket = SupportTicket::query()
            ->where('id_secure', $ticketSecureId)
            ->first();

        if (! $ticket) {
            throw (new ModelNotFoundException)->setModel(SupportTicket::class, [$ticketSecureId]);
        }

        $integration = PartnerIntegration::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->where('mlhub_user_id', (int) $ticket->uid)
            ->when(
                $ticket->team_id,
                fn ($query) => $query->where('mlhub_workspace_id', (int) $ticket->team_id)
            )
            ->whereNotNull('mlhub_user_id')
            ->first();

        if (! $integration) {
            throw (new ModelNotFoundException)->setModel(SupportTicket::class, [$ticketSecureId]);
        }

        return $integration;
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeTicket(SupportTicket $ticket): array
    {
        $context = Schema::hasTable('partner_support_ticket_contexts')
            ? PartnerSupportTicketContext::query()->where('support_ticket_id', $ticket->id)->first()
            : null;
        $contextData = is_array($context?->context) ? $context->context : [];
        $presetCode = $context?->request_code ?: ($contextData['preset_code'] ?? null);
        $ticketType = (string) ($contextData['ticket_type'] ?? 'support');

        return [
            'ticket_id' => $ticket->id_secure,
            'ticket_type' => $ticketType,
            'source' => (string) ($contextData['source'] ?? 'fizahub'),
            'preset_code' => $presetCode,
            'campaign_id' => $contextData['campaign_id'] ?? $context?->related_resource_id,
            'subject' => $ticket->title,
            'status' => $this->statusCode((int) $ticket->status),
            'last_message' => $this->lastMessageBody($ticket),
            'created_at' => $this->isoFromUnix($ticket->created),
            'updated_at' => $this->isoFromUnix($ticket->changed),
            'last_message_at' => $this->lastMessageAt($ticket),
            'unread_by_business' => ! (bool) $ticket->user_read,
        ];
    }

    /**
     * Support summary payload (ticket form metadata + counts).
     *
     * @return array<string, mixed>
     */
    public function supportSummary(PartnerIntegration $integration): array
    {
        $tickets = $this->tenantTicketQuery($integration)->get(['status', 'user_read']);

        return [
            'open' => $tickets->where('status', 1)->count(),
            'resolved' => $tickets->where('status', 2)->count(),
            'closed' => $tickets->where('status', 0)->count(),
            'unread_by_business' => $tickets->where('user_read', false)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function close(PartnerIntegration $integration, string $ticketSecureId, ?string $reason = null): array
    {
        $ticket = $this->findScopedTicketOrFail($integration, $ticketSecureId);

        if ((int) $ticket->status === 0) {
            throw PartnerApiException::make(
                'ticket_already_closed',
                __('Phiếu hỗ trợ này đã được đóng.'),
                409
            );
        }

        if ($reason !== null && trim($reason) !== '') {
            SupportComment::query()->create([
                'id_secure' => Str::random(32),
                'ticket_id' => $ticket->id,
                'user_id' => (int) $integration->mlhub_user_id,
                'comment' => $this->plainTextMessage('['.__('Đóng phiếu').'] '.$reason),
                'created' => time(),
                'changed' => time(),
            ]);
        }

        $ticket->forceFill([
            'status' => 0,
            'admin_read' => true,
            'user_read' => false,
            'changed' => time(),
        ])->save();

        $this->safeLog('partner.fizahub.support.close', 'Closed a FizaHUB support ticket.', [
            'subject_type' => SupportTicket::class,
            'subject_id' => $ticket->id,
            'area' => 'admin',
            'causer_user_id' => (int) $integration->mlhub_user_id,
            'metadata' => [
                'ticket' => $ticket->id_secure,
                'external_business_id' => $integration->external_business_id,
            ],
        ]);

        return $this->serializeTicket($ticket->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    public function reopen(PartnerIntegration $integration, string $ticketSecureId): array
    {
        $ticket = $this->findScopedTicketOrFail($integration, $ticketSecureId);

        if (! in_array((int) $ticket->status, [0, 2], true)) {
            throw PartnerApiException::make(
                'ticket_not_closed',
                __('Chỉ có thể mở lại phiếu đã đóng hoặc đã xử lý.'),
                409
            );
        }

        $ticket->forceFill([
            'status' => 1,
            'admin_read' => true,
            'user_read' => false,
            'changed' => time(),
        ])->save();

        $this->safeLog('partner.fizahub.support.reopen', 'Reopened a FizaHUB support ticket.', [
            'subject_type' => SupportTicket::class,
            'subject_id' => $ticket->id,
            'area' => 'admin',
            'causer_user_id' => (int) $integration->mlhub_user_id,
            'metadata' => [
                'ticket' => $ticket->id_secure,
                'external_business_id' => $integration->external_business_id,
            ],
        ]);

        return $this->serializeTicket($ticket->fresh());
    }

    /**
     * @return array<string, mixed>
     */
    public function storeAttachment(
        PartnerIntegration $integration,
        string $ticketSecureId,
        UploadedFile $file
    ): array {
        $ticket = $this->findScopedTicketOrFail($integration, $ticketSecureId);

        if (in_array((int) $ticket->status, [0, 2], true)) {
            throw new RuntimeException('ticket_not_open');
        }

        if (! Schema::hasTable('partner_support_attachments')) {
            throw PartnerApiException::make(
                'support_attachments_unavailable',
                __('Tính năng đính kèm tệp chưa sẵn sàng.'),
                503
            );
        }

        $maxBytes = max(1, (int) config('modules.apipartnerfizahub.support_max_attachment_size_mb', 10)) * 1024 * 1024;
        $allowedTypes = (array) config('modules.apipartnerfizahub.support_allowed_attachment_types', []);
        $mime = (string) ($file->getMimeType() ?: $file->getClientMimeType());

        if ($allowedTypes !== [] && ! in_array($mime, $allowedTypes, true)) {
            throw PartnerApiException::make(
                'attachment_type_not_allowed',
                __('Định dạng tệp đính kèm không được hỗ trợ.'),
                422,
                ['file' => [$mime]]
            );
        }

        if ($file->getSize() > $maxBytes) {
            throw PartnerApiException::make(
                'attachment_too_large',
                __('Tệp đính kèm vượt quá dung lượng cho phép.'),
                422
            );
        }

        $disk = 'local';
        $directory = 'partner-fizahub/support/'.$ticket->id;
        $path = $file->store($directory, $disk);

        $attachment = PartnerSupportAttachment::query()->create([
            'support_ticket_id' => $ticket->id,
            'id_secure' => Str::random(32),
            'original_name' => Str::limit((string) $file->getClientOriginalName(), 250, ''),
            'mime_type' => $mime,
            'size_bytes' => (int) $file->getSize(),
            'disk' => $disk,
            'path' => $path,
            'uploaded_by_user_id' => (int) $integration->mlhub_user_id,
        ]);

        $ticket->forceFill([
            'admin_read' => true,
            'user_read' => false,
            'changed' => time(),
        ])->save();

        $this->safeLog('partner.fizahub.support.attachment', 'Uploaded a FizaHUB support attachment.', [
            'subject_type' => SupportTicket::class,
            'subject_id' => $ticket->id,
            'area' => 'admin',
            'causer_user_id' => (int) $integration->mlhub_user_id,
            'metadata' => [
                'ticket' => $ticket->id_secure,
                'attachment' => $attachment->id_secure,
                'external_business_id' => $integration->external_business_id,
            ],
        ]);

        return [
            'attachment_id' => $attachment->id_secure,
            'original_name' => $attachment->original_name,
            'mime_type' => $attachment->mime_type,
            'size_bytes' => $attachment->size_bytes,
            'created_at' => $this->isoFromUnix(time()),
        ];
    }

    /** @return array{items: list<array<string, mixed>>} */
    public function presets(PartnerIntegration $integration): array
    {
        if (! Schema::hasTable('partner_support_presets')) {
            return ['items' => []];
        }

        $this->ensureDefaultPresets();
        $items = PartnerSupportPreset::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->where('is_active', true)
            ->where('code', '!=', 'fizahub_onboarding')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->filter(function (PartnerSupportPreset $preset) use ($integration): bool {
                $allowed = $preset->allowed_package_codes;

                return ! is_array($allowed)
                    || $allowed === []
                    || in_array($integration->package_code, $allowed, true);
            })
            ->map(fn (PartnerSupportPreset $preset): array => $this->serializePreset($preset))
            ->values()
            ->all();

        return ['items' => $items];
    }

    public function findPreset(string $code): ?PartnerSupportPreset
    {
        if (! Schema::hasTable('partner_support_presets')) {
            return null;
        }

        $this->ensureDefaultPresets();

        return PartnerSupportPreset::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->where('code', $code)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Seed the default support presets when none exist yet.
     */
    public function ensureDefaultPresets(): void
    {
        if (! Schema::hasTable('partner_support_presets')) {
            return;
        }

        $partnerCode = $this->mapping->partnerCode();

        foreach ($this->defaultPresets() as $preset) {
            PartnerSupportPreset::query()->firstOrCreate(
                ['partner_code' => $partnerCode, 'code' => $preset['code']],
                array_merge($preset, ['partner_code' => $partnerCode])
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function defaultPresets(): array
    {
        return [
            [
                'code' => 'fizahub_onboarding',
                'name' => 'FizaHUB onboarding',
                'description' => 'Theo dõi tiếp nhận và cấu hình tài khoản FizaHUB.',
                'category_id' => null,
                'type_id' => null,
                'default_subject' => 'FizaHUB onboarding',
                'message_template' => 'Doanh nghiệp đang chờ tư vấn và cấu hình Marketing.',
                'required_fields' => [],
                'allowed_package_codes' => null,
                'sla_hours' => 24,
                'sort_order' => 0,
                'is_active' => true,
            ],
            [
                'code' => 'qr_scan_not_recorded',
                'name' => 'QR check-in không ghi nhận lượt quét',
                'description' => 'Doanh nghiệp phản ánh mã QR check-in không phát sinh lượt quét nào.',
                'category_id' => null,
                'type_id' => null,
                'default_subject' => 'QR check-in không ghi nhận lượt quét',
                'message_template' => "Doanh nghiệp phản ánh mã QR check-in không ghi nhận lượt quét.\nTài nguyên liên quan: :related_resource\nChi tiết: :details",
                'required_fields' => ['related_resource'],
                'allowed_package_codes' => null,
                'sla_hours' => 24,
                'sort_order' => 10,
                'is_active' => true,
            ],
            [
                'code' => 'growth_recommendation',
                'name' => 'Tư vấn đề xuất tăng trưởng',
                'description' => 'Yêu cầu MLHUB hỗ trợ triển khai đề xuất tăng trưởng.',
                'category_id' => null,
                'type_id' => null,
                'default_subject' => 'Tư vấn đề xuất tăng trưởng',
                'message_template' => 'Doanh nghiệp cần tư vấn triển khai đề xuất tăng trưởng.',
                'required_fields' => [],
                'allowed_package_codes' => null,
                'sla_hours' => 24,
                'sort_order' => 20,
                'is_active' => true,
            ],
            [
                'code' => 'campaign_request',
                'name' => 'Yêu cầu điều chỉnh chiến dịch',
                'description' => 'Yêu cầu MLHUB rà soát một chiến dịch cụ thể.',
                'category_id' => null,
                'type_id' => null,
                'default_subject' => 'Yêu cầu điều chỉnh chiến dịch',
                'message_template' => 'Doanh nghiệp yêu cầu điều chỉnh chiến dịch.',
                'required_fields' => ['campaign_id'],
                'allowed_package_codes' => null,
                'sla_hours' => 24,
                'sort_order' => 30,
                'is_active' => true,
            ],
            [
                'code' => 'package_upgrade',
                'name' => 'Tư vấn nâng gói',
                'description' => 'Yêu cầu tư vấn gói dịch vụ phù hợp.',
                'category_id' => null,
                'type_id' => null,
                'default_subject' => 'Tư vấn nâng gói dịch vụ',
                'message_template' => 'Doanh nghiệp cần tư vấn nâng gói dịch vụ.',
                'required_fields' => [],
                'allowed_package_codes' => null,
                'sla_hours' => 24,
                'sort_order' => 40,
                'is_active' => true,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePreset(PartnerSupportPreset $preset): array
    {
        return [
            'preset_code' => $preset->code,
            'subject' => $preset->default_subject ?: $preset->name,
            'subject_locked' => true,
            'description' => $preset->description,
            'ticket_type' => $this->presetTicketType($preset->code),
            'required_context' => $preset->required_fields ?? [],
            'sla_hours' => $preset->sla_hours,
            'response_channels' => ['in_app', 'phone'],
            'requires_campaign' => $preset->code === 'campaign_request',
        ];
    }

    /**
     * @param  array<string, mixed>  $relatedResource
     * @param  array<string, mixed>  $details
     */
    private function buildPresetMessage(
        PartnerSupportPreset $preset,
        array $relatedResource,
        array $details
    ): string {
        $relatedText = $relatedResource !== []
            ? json_encode($relatedResource, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '-';
        $detailsText = $details !== []
            ? json_encode($details, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : '-';

        $template = (string) ($preset->message_template ?: $preset->name);

        return $this->plainTextMessage(strtr($template, [
            ':related_resource' => (string) $relatedText,
            ':details' => (string) $detailsText,
        ]));
    }

    /** @param array<string, mixed> $metadata */
    private function storeContext(
        PartnerIntegration $integration,
        SupportTicket $ticket,
        string $presetCode,
        string $campaignId,
        string $responseChannel,
        array $metadata
    ): void {
        if (! Schema::hasTable('partner_support_ticket_contexts')) {
            return;
        }

        PartnerSupportTicketContext::query()->updateOrCreate(
            ['support_ticket_id' => $ticket->id],
            [
                'partner_integration_id' => $integration->id,
                'external_business_id' => $integration->external_business_id,
                'request_code' => $presetCode !== '' ? $presetCode : null,
                'package_code' => $integration->package_code,
                'related_resource_type' => $campaignId !== '' ? QrCampaign::class : null,
                'related_resource_id' => $campaignId !== '' ? $campaignId : null,
                'context' => [
                    'ticket_type' => $this->presetTicketType($presetCode),
                    'source' => 'fizahub',
                    'preset_code' => $presetCode !== '' ? $presetCode : null,
                    'campaign_id' => $campaignId !== '' ? $campaignId : null,
                    'response_channel' => $responseChannel,
                    'metadata' => $metadata,
                ],
            ]
        );
    }

    public function plainTextMessage(string $message): string
    {
        $plain = html_entity_decode(strip_tags($message), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $plain = trim(preg_replace("/[ \t]+/u", ' ', $plain) ?? $plain);
        $plain = trim(preg_replace("/\n{3,}/u", "\n\n", $plain) ?? $plain);

        return mb_substr($plain, 0, 5000);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function serializeMessages(SupportTicket $ticket, ?CarbonImmutable $since): array
    {
        $messages = [];

        $initialCreated = (int) ($ticket->created ?: 0);
        if ($since === null || $initialCreated > $since->timestamp) {
            $messages[] = [
                'message_id' => $ticket->id_secure.':initial',
                'sender_type' => 'business',
                'body' => (string) $ticket->content,
                'created_at' => $this->isoFromUnix($ticket->created),
            ];
        }

        $comments = $ticket->comments()
            ->when($since !== null, function ($query) use ($since): void {
                $query->where('created', '>', $since->timestamp);
            })
            ->orderBy('created')
            ->orderBy('id')
            ->get();

        foreach ($comments as $comment) {
            $messages[] = $this->serializeComment($ticket, $comment);
        }

        return $messages;
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeComment(SupportTicket $ticket, SupportComment $comment): array
    {
        return [
            'message_id' => $comment->id_secure,
            'sender_type' => ((int) $comment->user_id === (int) $ticket->uid) ? 'business' : 'admin',
            'body' => (string) $comment->comment,
            'created_at' => $this->isoFromUnix($comment->created),
        ];
    }

    private function lastMessageAt(SupportTicket $ticket): ?string
    {
        $latestComment = (int) $ticket->comments()->max('created');
        $latest = max((int) ($ticket->changed ?: 0), (int) ($ticket->created ?: 0), $latestComment);

        return $latest > 0 ? $this->isoFromUnix($latest) : null;
    }

    private function lastMessageBody(SupportTicket $ticket): string
    {
        $comment = $ticket->comments()
            ->orderByDesc('created')
            ->orderByDesc('id')
            ->first();

        return (string) ($comment?->comment ?? $ticket->content);
    }

    private function tenantTicketQuery(PartnerIntegration $integration): \Illuminate\Database\Eloquent\Builder
    {
        return SupportTicket::query()
            ->where('uid', (int) $integration->mlhub_user_id)
            ->where(function ($query) use ($integration): void {
                $query->where('team_id', (int) $integration->mlhub_workspace_id)
                    ->orWhereNull('team_id');
            });
    }

    private function databaseStatus(string $status): int
    {
        return match ($status) {
            'resolved' => 2,
            'closed' => 0,
            default => 1,
        };
    }

    private function presetTicketType(string $presetCode): string
    {
        return match ($presetCode) {
            'fizahub_onboarding' => 'onboarding',
            'campaign_request' => 'campaign_request',
            'package_upgrade' => 'package_upgrade',
            default => 'support',
        };
    }

    private function statusCode(int $status): string
    {
        return match ($status) {
            2 => 'resolved',
            0 => 'closed',
            default => 'open',
        };
    }

    private function isoFromUnix(null|int|string $unix): ?string
    {
        $unix = (int) $unix;

        if ($unix <= 0) {
            return null;
        }

        return CarbonImmutable::createFromTimestamp($unix)->utc()->toIso8601String();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function safeLog(string $event, string $description, array $attributes): void
    {
        if (! Schema::hasTable('audit_logs') || ! function_exists('log_activity')) {
            return;
        }

        try {
            log_activity($event, $description, $attributes);
        } catch (\Throwable) {
            // Audit logging must not block partner support flows.
        }
    }
}
