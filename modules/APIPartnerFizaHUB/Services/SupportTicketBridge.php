<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminSupport\Models\SupportComment;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportAttachment;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportPreset;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportTicketContext;
use Modules\APIPartnerFizaHUB\Support\PartnerApiException;
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
        $requestCode = isset($input['request_code']) ? trim((string) $input['request_code']) : '';
        $relatedResource = (array) ($input['related_resource'] ?? []);
        $details = (array) ($input['details'] ?? []);

        $subject = isset($input['subject']) ? trim((string) $input['subject']) : '';
        $message = isset($input['message']) ? (string) $input['message'] : '';
        $categoryId = $input['category_id'] ?? null;
        $typeId = $input['type_id'] ?? null;
        $preset = null;

        if ($requestCode !== '') {
            $preset = $this->findPreset($requestCode);

            if (! $preset) {
                throw PartnerApiException::make(
                    'support_preset_not_found',
                    __('Yêu cầu hỗ trợ không hợp lệ hoặc đã ngừng sử dụng.'),
                    422,
                    ['request_code' => [$requestCode]]
                );
            }

            if ($subject === '') {
                $subject = (string) ($preset->default_subject ?: $preset->name);
            }

            if (trim($message) === '') {
                $message = $this->buildPresetMessage($preset, $relatedResource, $details);
            }

            $categoryId = $categoryId ?? $preset->category_id;
            $typeId = $typeId ?? $preset->type_id;
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
            'category_id' => $categoryId,
            'type_id' => $typeId,
        ]);

        $this->storeContext($integration, $ticket, $requestCode, $relatedResource, $details);

        return $ticket;
    }

    public function list(
        PartnerIntegration $integration,
        int $page = 1,
        int $perPage = 20
    ): LengthAwarePaginator {
        $perPage = max(1, min(100, $perPage));
        $page = max(1, $page);

        return SupportTicket::query()
            ->where('uid', (int) $integration->mlhub_user_id)
            ->where(function ($query) use ($integration): void {
                $query->where('team_id', (int) $integration->mlhub_workspace_id)
                    ->orWhereNull('team_id');
            })
            ->orderByDesc('changed')
            ->orderByDesc('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * @param  list<array{type: string, id: int}>  $duplicates
     */
    public function createOnboardingReviewTicket(
        PartnerOnboardingRequest $onboarding,
        string $title,
        string $summary,
        array $duplicates = []
    ): SupportTicket {
        $content = json_encode([
            'summary' => $summary,
            'request_id' => $onboarding->request_id,
            'external_business_id' => $onboarding->external_business_id,
            'package_code' => $onboarding->package_code,
            'status' => $onboarding->status,
            'verification_status' => $onboarding->verification_status,
            'duplicate_check' => $duplicates,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: $summary;

        if ($onboarding->support_ticket_id) {
            $existing = SupportTicket::query()->find($onboarding->support_ticket_id);

            if ($existing) {
                $existing->forceFill([
                    'title' => $title,
                    'content' => $content,
                    'changed' => time(),
                    'admin_read' => true,
                    'user_read' => false,
                    'status' => 1,
                ])->save();

                return $existing;
            }
        }

        return SupportTicket::query()->create([
            'id_secure' => Str::random(32),
            // No MLHUB user yet — admin queue only (unsigned id, no FK).
            // Admin UI already nullsafes missing users as "Unknown user".
            'uid' => 0,
            'open_by' => 0,
            'team_id' => null,
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

        return [
            'ticket' => $this->serializeTicket($ticket),
            'messages' => $messages,
            'next_poll_after_seconds' => 15,
            'last_message_at' => $this->lastMessageAt($ticket),
        ];
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
        return [
            'ticket_id' => $ticket->id_secure,
            'subject' => $ticket->title,
            'status' => $this->statusCode((int) $ticket->status),
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
        $this->ensureDefaultPresets();

        $maxSize = max(1, (int) config('modules.apipartnerfizahub.support_max_attachment_size_mb', 10));
        $allowedTypes = (array) config('modules.apipartnerfizahub.support_allowed_attachment_types', []);
        $categories = (array) config('modules.apipartnerfizahub.support_categories', []);

        $openStatuses = [1];
        $tickets = SupportTicket::query()
            ->where('uid', (int) $integration->mlhub_user_id)
            ->get(['status']);

        return [
            'counts' => [
                'total' => $tickets->count(),
                'open' => $tickets->whereIn('status', $openStatuses)->count(),
                'resolved' => $tickets->where('status', 2)->count(),
                'closed' => $tickets->where('status', 0)->count(),
            ],
            'ticket_form' => [
                'categories' => array_values($categories),
                'request_presets' => $this->presets()
                    ->map(fn (PartnerSupportPreset $preset): array => $this->serializePreset($preset))
                    ->values()
                    ->all(),
                'allowed_attachment_types' => array_values($allowedTypes),
                'max_attachment_size_mb' => $maxSize,
            ],
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

    /**
     * @return Collection<int, PartnerSupportPreset>
     */
    public function presets(): Collection
    {
        if (! Schema::hasTable('partner_support_presets')) {
            return collect();
        }

        return PartnerSupportPreset::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
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
                'code' => 'qr_checkin_no_scans',
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serializePreset(PartnerSupportPreset $preset): array
    {
        return [
            'request_code' => $preset->code,
            'name' => $preset->name,
            'description' => $preset->description,
            'required_fields' => $preset->required_fields ?? [],
            'sla_hours' => $preset->sla_hours,
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

    /**
     * @param  array<string, mixed>  $relatedResource
     * @param  array<string, mixed>  $details
     */
    private function storeContext(
        PartnerIntegration $integration,
        SupportTicket $ticket,
        string $requestCode,
        array $relatedResource,
        array $details
    ): void {
        if (! Schema::hasTable('partner_support_ticket_contexts')) {
            return;
        }

        PartnerSupportTicketContext::query()->updateOrCreate(
            ['support_ticket_id' => $ticket->id],
            [
                'partner_integration_id' => $integration->id,
                'external_business_id' => $integration->external_business_id,
                'request_code' => $requestCode !== '' ? $requestCode : null,
                'package_code' => $integration->package_code,
                'related_resource_type' => isset($relatedResource['type']) ? (string) $relatedResource['type'] : null,
                'related_resource_id' => isset($relatedResource['id']) ? (string) $relatedResource['id'] : null,
                'context' => [
                    'related_resource' => $relatedResource,
                    'details' => $details,
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
