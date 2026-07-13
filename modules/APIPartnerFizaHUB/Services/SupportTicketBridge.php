<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminSupport\Models\SupportComment;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
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
