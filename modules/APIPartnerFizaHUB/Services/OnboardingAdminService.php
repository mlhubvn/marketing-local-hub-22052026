<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\User;
use Modules\AdminUser\Support\UserDeletionStorageCleanup;
use Modules\APIPartnerFizaHUB\Models\PartnerApiLog;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingStatusHistory;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportAttachment;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportTicketContext;
use Modules\APIPartnerFizaHUB\Models\PartnerWebhookOutbox;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Throwable;

class OnboardingAdminService
{
    public function __construct(
        protected PartnerMappingService $mapping,
        protected WebhookOutboxService $webhooks,
        protected PackageAssignmentService $packages,
        protected UserDeletionStorageCleanup $storageCleanup,
    ) {}

    public function transition(
        PartnerOnboardingRequest $onboarding,
        string $toStatus,
        string $changedByType = 'admin',
        ?int $changedById = null,
        ?string $reason = null,
        array $metadata = []
    ): PartnerOnboardingRequest {
        OnboardingStatusMachine::assertCanTransition($onboarding->status, $toStatus);

        return $this->applyStatusChange($onboarding, $toStatus, $changedByType, $changedById, $reason, $metadata);
    }

    /**
     * Admin board override: set any public status (including reactivation from cancelled/completed).
     * Partner API / normal transitions still use assertCanTransition via transition().
     */
    public function adminSetStatus(
        PartnerOnboardingRequest $onboarding,
        string $toStatus,
        ?int $changedById = null,
        ?string $reason = null,
        array $metadata = []
    ): PartnerOnboardingRequest {
        if (! in_array($toStatus, OnboardingStatusMachine::PUBLIC_STATUSES, true)) {
            throw new InvalidArgumentException(__('The selected stage is not allowed from the current status.'));
        }

        $fromPublic = OnboardingStatusMachine::publicStatus((string) $onboarding->status);

        if ($fromPublic === $toStatus && (string) $onboarding->status === $toStatus) {
            return $onboarding->fresh(['supportTicket', 'consultant', 'business', 'user', 'statusHistories']) ?? $onboarding;
        }

        return $this->applyStatusChange(
            $onboarding,
            $toStatus,
            'admin',
            $changedById,
            $reason,
            array_merge(['admin_override' => true], $metadata)
        );
    }

    public function adminAssignPackage(
        PartnerOnboardingRequest $onboarding,
        string $packageCode,
        ?int $changedById = null,
        ?string $reason = null
    ): PartnerOnboardingRequest {
        $allowed = array_keys((array) config('modules.apipartnerfizahub.package_map', []));

        if (! in_array($packageCode, $allowed, true)) {
            throw new InvalidArgumentException(__('Please choose a valid package.'));
        }

        $integration = $this->integrationFor($onboarding);

        if (! $integration) {
            throw new InvalidArgumentException(__('Integration mapping is missing for this onboarding request.'));
        }

        $this->packages->assignEffectivePackage(
            $integration,
            $packageCode,
            $changedById,
            $reason ?: __('Package updated by admin.'),
            $onboarding,
            true
        );

        return $onboarding->fresh(['supportTicket', 'consultant', 'business', 'user', 'statusHistories']) ?? $onboarding;
    }

    /**
     * @param  array<string, mixed>  $metadata
     */
    protected function applyStatusChange(
        PartnerOnboardingRequest $onboarding,
        string $toStatus,
        string $changedByType = 'admin',
        ?int $changedById = null,
        ?string $reason = null,
        array $metadata = []
    ): PartnerOnboardingRequest {
        return DB::transaction(function () use ($onboarding, $toStatus, $changedByType, $changedById, $reason, $metadata) {
            $from = (string) $onboarding->status;
            $now = now();

            $updates = [
                'status' => $toStatus,
                'current_step' => OnboardingStatusMachine::defaultStepFor($toStatus),
                'admin_status' => $toStatus,
            ];

            if ($reason !== null && $reason !== '') {
                $updates['review_note'] = $reason;
            }

            if ($toStatus === OnboardingStatusMachine::CONSULTING && ! $onboarding->consultant_contacted_at) {
                $updates['consultant_contacted_at'] = $now;
            }

            if ($toStatus === OnboardingStatusMachine::READY) {
                $updates['ready_at'] = $now;
            }

            if ($toStatus === OnboardingStatusMachine::COMPLETED) {
                $updates['completed_at'] = $now;
                if (! $onboarding->ready_at) {
                    $updates['ready_at'] = $now;
                }
            }

            if ($toStatus === OnboardingStatusMachine::CANCELLED) {
                $updates['cancelled_at'] = $now;
            }

            $onboarding->forceFill($updates)->save();

            PartnerOnboardingStatusHistory::query()->create([
                'onboarding_request_id' => $onboarding->id,
                'from_status' => $from,
                'to_status' => $toStatus,
                'changed_by_type' => $changedByType,
                'changed_by_id' => $changedById,
                'reason' => $reason,
                'metadata' => $metadata,
                'created_at' => $now,
            ]);

            $this->safeAudit('partner.fizahub.onboarding.transition', 'Updated FizaHUB onboarding status.', [
                'subject_type' => PartnerOnboardingRequest::class,
                'subject_id' => $onboarding->id,
                'area' => 'admin',
                'causer_user_id' => $changedById,
                'metadata' => [
                    'from' => $from,
                    'to' => $toStatus,
                    'reason' => $reason,
                    'request_id' => $onboarding->request_id,
                    'admin_override' => (bool) ($metadata['admin_override'] ?? false),
                ],
            ]);

            $fresh = $onboarding->fresh(['supportTicket', 'consultant', 'business', 'user']);
            $integration = $this->integrationFor($fresh);

            if ($integration) {
                $this->webhooks->queueOnboardingStatus(
                    $this->webhookPayload($fresh, $integration),
                    $fresh->request_id.'|'.$from.'|'.$toStatus.'|'.$now->timestamp
                );

                $fresh->forceFill(['last_synced_at' => $now])->save();
            }

            return $fresh->fresh(['supportTicket', 'consultant', 'business', 'user', 'statusHistories']);
        });
    }

    public function updateDetails(
        PartnerOnboardingRequest $onboarding,
        array $input,
        ?int $changedById = null
    ): PartnerOnboardingRequest {
        return DB::transaction(function () use ($onboarding, $input, $changedById) {
            $statusChanged = false;
            $packageChanged = false;

            if (array_key_exists('assigned_consultant_id', $input)) {
                $onboarding->assigned_consultant_id = $input['assigned_consultant_id'] !== null && $input['assigned_consultant_id'] !== ''
                    ? (int) $input['assigned_consultant_id']
                    : null;
            }

            if (array_key_exists('review_note', $input)) {
                $onboarding->review_note = $input['review_note'];
            }

            $onboarding->save();

            if (! empty($input['status']) && (string) $input['status'] !== (string) $onboarding->status) {
                $onboarding = $this->transition(
                    $onboarding,
                    (string) $input['status'],
                    'admin',
                    $changedById,
                    $input['reason'] ?? $onboarding->review_note
                );
                $statusChanged = true;
            }

            if (! empty($input['approved_package_code'])) {
                $integration = $this->integrationFor($onboarding);

                if ($integration) {
                    $this->packages->assignEffectivePackage(
                        $integration,
                        (string) $input['approved_package_code'],
                        $changedById,
                        $input['package_reason'] ?? 'Admin approved package change.',
                        $onboarding,
                        true
                    );
                    $packageChanged = true;
                }
            }

            if (! $statusChanged && (array_key_exists('assigned_consultant_id', $input) || $packageChanged)) {
                $integration = $this->integrationFor($onboarding);

                if ($integration) {
                    $this->webhooks->queueOnboardingStatus(
                        $this->webhookPayload($onboarding->fresh(), $integration),
                        $onboarding->request_id.'|details|'.now()->timestamp
                    );
                    $onboarding->forceFill(['last_synced_at' => now()])->save();
                }
            }

            $this->safeAudit('partner.fizahub.onboarding.update', 'Updated FizaHUB onboarding details.', [
                'subject_type' => PartnerOnboardingRequest::class,
                'subject_id' => $onboarding->id,
                'area' => 'admin',
                'causer_user_id' => $changedById,
                'metadata' => [
                    'request_id' => $onboarding->request_id,
                    'fields' => array_keys($input),
                ],
            ]);

            return $onboarding->fresh(['supportTicket', 'consultant', 'business', 'user', 'statusHistories']);
        });
    }

    public function markContacted(PartnerOnboardingRequest $onboarding, ?int $adminId = null): PartnerOnboardingRequest
    {
        if ($onboarding->status === OnboardingStatusMachine::AWAITING_CONSULTANT
            || $onboarding->status === OnboardingStatusMachine::NEEDS_REVIEW
            || $onboarding->status === 'pending_verification'
            || $onboarding->status === 'duplicate_review'
        ) {
            return $this->transition(
                $onboarding,
                OnboardingStatusMachine::CONSULTING,
                'admin',
                $adminId,
                'Consultant contacted the business.'
            );
        }

        $onboarding->forceFill([
            'consultant_contacted_at' => $onboarding->consultant_contacted_at ?: now(),
        ])->save();

        return $onboarding->fresh();
    }

    public function requestInformation(PartnerOnboardingRequest $onboarding, ?string $note = null, ?int $adminId = null): PartnerOnboardingRequest
    {
        return $this->transition(
            $onboarding,
            OnboardingStatusMachine::NEEDS_INFORMATION,
            'admin',
            $adminId,
            $note ?: 'Additional information requested.'
        );
    }

    public function startConfiguring(PartnerOnboardingRequest $onboarding, ?int $adminId = null): PartnerOnboardingRequest
    {
        return $this->transition(
            $onboarding,
            OnboardingStatusMachine::CONFIGURING,
            'admin',
            $adminId,
            'Started marketing configuration.'
        );
    }

    public function markReady(PartnerOnboardingRequest $onboarding, ?int $adminId = null): PartnerOnboardingRequest
    {
        return $this->transition(
            $onboarding,
            OnboardingStatusMachine::READY,
            'admin',
            $adminId,
            'Account is ready for use.'
        );
    }

    public function markCompleted(PartnerOnboardingRequest $onboarding, ?int $adminId = null): PartnerOnboardingRequest
    {
        return $this->transition(
            $onboarding,
            OnboardingStatusMachine::COMPLETED,
            'admin',
            $adminId,
            'Onboarding completed.'
        );
    }

    public function cancel(PartnerOnboardingRequest $onboarding, ?string $reason = null, ?int $adminId = null): PartnerOnboardingRequest
    {
        return $this->transition(
            $onboarding,
            OnboardingStatusMachine::CANCELLED,
            'admin',
            $adminId,
            $reason ?: 'Onboarding cancelled by admin.'
        );
    }

    public function resendWebhook(PartnerOnboardingRequest $onboarding): bool
    {
        $integration = $this->integrationFor($onboarding);

        if (! $integration) {
            throw new InvalidArgumentException('Integration mapping is missing for this onboarding request.');
        }

        $row = $this->webhooks->queueOnboardingStatus(
            $this->webhookPayload($onboarding, $integration),
            $onboarding->request_id.'|resend|'.now()->timestamp
        );

        $onboarding->forceFill(['last_synced_at' => now()])->save();

        return $row !== null;
    }

    /**
     * Purge partner onboarding + integration mapping for this business.
     * Keeps the MLHUB user / team / business (delete those from Users admin).
     *
     * @return array{external_business_id: string, request_ids: list<string>, tickets_deleted: int}
     */
    public function adminPurgeOnboarding(
        PartnerOnboardingRequest $onboarding,
        ?int $changedById = null
    ): array {
        return DB::transaction(fn (): array => $this->purgePartnerBusiness(
            (string) $onboarding->partner_code,
            (string) $onboarding->external_business_id,
            $changedById,
            (int) $onboarding->id,
            $onboarding->mlhub_user_id ? (int) $onboarding->mlhub_user_id : null
        ));
    }

    /**
     * Remove every FizaHUB mapping that still points at the user or one of the
     * user's businesses. This must run before the user/business rows are deleted,
     * otherwise nullable foreign keys preserve orphaned onboarding records.
     *
     * @return array{businesses_purged: int, request_ids: list<string>, tickets_deleted: int}
     */
    public function adminPurgeForUser(int $userId, ?int $changedById = null): array
    {
        if (! Schema::hasTable('partner_onboarding_requests')
            || ! Schema::hasTable('partner_integrations')) {
            return [
                'businesses_purged' => 0,
                'request_ids' => [],
                'tickets_deleted' => 0,
            ];
        }

        $purge = function () use ($userId, $changedById): array {
            $businessIds = Schema::hasTable('lb_businesses')
                ? DB::table('lb_businesses')
                    ->where('user_id', $userId)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all()
                : [];

            $scopes = collect();

            $onboardingQuery = PartnerOnboardingRequest::query()
                ->where(function ($query) use ($userId, $businessIds): void {
                    $query->where('mlhub_user_id', $userId);

                    if ($businessIds !== []) {
                        $query->orWhereIn('mlhub_business_id', $businessIds);
                    }
                });

            foreach ($onboardingQuery->get(['partner_code', 'external_business_id']) as $request) {
                $scopes->put(
                    $request->partner_code.'|'.$request->external_business_id,
                    [(string) $request->partner_code, (string) $request->external_business_id]
                );
            }

            $integrationQuery = PartnerIntegration::query()
                ->where(function ($query) use ($userId, $businessIds): void {
                    $query->where('mlhub_user_id', $userId);

                    if ($businessIds !== []) {
                        $query->orWhereIn('mlhub_business_id', $businessIds);
                    }
                });

            foreach ($integrationQuery->get(['partner_code', 'external_business_id']) as $integration) {
                $scopes->put(
                    $integration->partner_code.'|'.$integration->external_business_id,
                    [(string) $integration->partner_code, (string) $integration->external_business_id]
                );
            }

            $requestIds = [];
            $ticketsDeleted = 0;

            foreach ($scopes->values() as [$partnerCode, $externalBusinessId]) {
                $result = $this->purgePartnerBusiness(
                    $partnerCode,
                    $externalBusinessId,
                    $changedById,
                    null,
                    $userId
                );

                $requestIds = array_merge($requestIds, $result['request_ids']);
                $ticketsDeleted += $result['tickets_deleted'];
            }

            return [
                'businesses_purged' => $scopes->count(),
                'request_ids' => array_values(array_unique($requestIds)),
                'tickets_deleted' => $ticketsDeleted,
            ];
        };

        return DB::transactionLevel() > 0
            ? $purge()
            : DB::transaction($purge);
    }

    public function resolveUserForDeletion(PartnerOnboardingRequest $onboarding): User
    {
        $candidateIds = collect();

        if ((int) $onboarding->mlhub_user_id > 0) {
            $candidateIds->push((int) $onboarding->mlhub_user_id);
        }

        $integration = PartnerIntegration::query()
            ->where('partner_code', (string) $onboarding->partner_code)
            ->where('external_business_id', (string) $onboarding->external_business_id)
            ->first();

        if ($integration && (int) $integration->mlhub_user_id > 0) {
            $candidateIds->push((int) $integration->mlhub_user_id);
        }

        $businessIds = collect([
            $onboarding->mlhub_business_id,
            $integration?->mlhub_business_id,
        ])->map(fn ($id) => (int) $id)->filter()->unique()->values();

        if ($businessIds->isNotEmpty() && Schema::hasTable('lb_businesses')
            && Schema::hasColumn('lb_businesses', 'user_id')) {
            $candidateIds = $candidateIds->merge(
                DB::table('lb_businesses')
                    ->whereIn('id', $businessIds)
                    ->pluck('user_id')
                    ->map(fn ($id) => (int) $id)
            );
        }

        $candidateIds = $candidateIds
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values();

        if ($candidateIds->count() > 1) {
            throw new InvalidArgumentException(
                __('The onboarding mapping points to multiple MLHUB users. No account was deleted.')
            );
        }

        $userId = (int) ($candidateIds->first() ?? 0);
        $user = $userId > 0 ? User::query()->find($userId) : null;

        if (! $user) {
            throw new InvalidArgumentException(
                __('No unambiguous MLHUB user could be resolved from this onboarding mapping.')
            );
        }

        return $user;
    }

    /**
     * @return array{external_business_id: string, request_ids: list<string>, tickets_deleted: int}
     */
    private function purgePartnerBusiness(
        string $partnerCode,
        string $externalBusinessId,
        ?int $changedById = null,
        ?int $subjectId = null,
        ?int $mlhubUserId = null
    ): array {
        $requests = PartnerOnboardingRequest::query()
            ->where('partner_code', $partnerCode)
            ->where('external_business_id', $externalBusinessId)
            ->get();

        $requestIds = $requests->pluck('request_id')->map(fn ($id) => (string) $id)->filter()->values()->all();
        $integration = PartnerIntegration::query()
            ->where('partner_code', $partnerCode)
            ->where('external_business_id', $externalBusinessId)
            ->first();

        $ticketIds = $requests->pluck('support_ticket_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if ($integration && Schema::hasTable('partner_support_ticket_contexts')) {
            $ticketIds = array_values(array_unique(array_merge(
                $ticketIds,
                PartnerSupportTicketContext::query()
                    ->where('partner_integration_id', $integration->id)
                    ->pluck('support_ticket_id')
                    ->map(fn ($id) => (int) $id)
                    ->all()
            )));
        }

        // Detach FK before deleting tickets.
        PartnerOnboardingRequest::query()
            ->whereIn('id', $requests->pluck('id'))
            ->update(['support_ticket_id' => null]);

        if (Schema::hasTable('partner_api_logs')) {
            PartnerApiLog::query()
                ->where('partner_code', $partnerCode)
                ->where(function ($query) use ($requestIds, $externalBusinessId): void {
                    if ($requestIds !== []) {
                        $query->whereIn('request_id', $requestIds);
                    }

                    $query
                        ->orWhere('request_payload->external_business_id', $externalBusinessId)
                        ->orWhere('response_payload->data->external_business_id', $externalBusinessId);
                })
                ->delete();
        }

        if (Schema::hasTable('partner_webhook_outbox')) {
            PartnerWebhookOutbox::query()
                ->where('partner_code', $partnerCode)
                ->where(function ($query) use ($requestIds, $externalBusinessId): void {
                    foreach ($requestIds as $requestId) {
                        $query->orWhere('dedupe_key', 'like', $requestId.'%');
                    }

                    $query->orWhere('payload->external_business_id', $externalBusinessId);
                })
                ->delete();
        }

        $subjectUserId = $mlhubUserId
            ?: ($requests->first()?->mlhub_user_id
                ? (int) $requests->first()->mlhub_user_id
                : ($integration?->mlhub_user_id ? (int) $integration->mlhub_user_id : 0));

        $this->deleteSupportTickets($ticketIds, $subjectUserId);

        // Histories cascade with onboarding requests.
        PartnerOnboardingRequest::query()
            ->where('partner_code', $partnerCode)
            ->where('external_business_id', $externalBusinessId)
            ->delete();

        if ($integration) {
            DB::afterCommit(function () use ($integration): void {
                app(DashboardService::class)->forgetForPartnerBusiness(
                    (string) $integration->partner_code,
                    (string) $integration->external_business_id,
                );
            });

            // Cascades: one-time logins, package assignments, remaining ticket contexts.
            $integration->delete();
        }

        $this->safeAudit('partner.fizahub.onboarding.purge', 'Purged FizaHUB partner onboarding data.', [
            'subject_type' => PartnerOnboardingRequest::class,
            'subject_id' => $subjectId ?: $requests->first()?->id,
            'area' => 'admin',
            'causer_user_id' => $changedById,
            'metadata' => [
                'external_business_id' => $externalBusinessId,
                'request_ids' => $requestIds,
                'tickets_deleted' => count($ticketIds),
                'integration_id' => $integration?->id,
                'mlhub_user_id' => $mlhubUserId
                    ?: ($requests->first()?->mlhub_user_id
                        ? (int) $requests->first()->mlhub_user_id
                        : ($integration?->mlhub_user_id ? (int) $integration->mlhub_user_id : null)),
            ],
        ]);

        return [
            'external_business_id' => $externalBusinessId,
            'request_ids' => $requestIds,
            'tickets_deleted' => count($ticketIds),
        ];
    }

    /**
     * @param  list<int>  $ticketIds
     */
    private function deleteSupportTickets(array $ticketIds, int $subjectUserId = 0): void
    {
        $ticketIds = array_values(array_unique(array_filter($ticketIds)));

        if ($ticketIds === []) {
            return;
        }

        if (Schema::hasTable('support_comments')) {
            DB::table('support_comments')->whereIn('ticket_id', $ticketIds)->delete();
        }

        if (Schema::hasTable('support_map_labels')) {
            DB::table('support_map_labels')->whereIn('ticket_id', $ticketIds)->delete();
        }

        if (Schema::hasTable('partner_support_attachments')) {
            $storedAttachments = PartnerSupportAttachment::query()
                ->whereIn('support_ticket_id', $ticketIds)
                ->get(['disk', 'path'])
                ->map(fn (PartnerSupportAttachment $attachment): array => [
                    'disk' => (string) $attachment->disk,
                    'path' => (string) $attachment->path,
                ])
                ->all();

            if ($storedAttachments !== []) {
                DB::afterCommit(function () use ($storedAttachments, $subjectUserId): void {
                    foreach ($storedAttachments as $attachment) {
                        if ($attachment['disk'] === '' || $attachment['path'] === '') {
                            continue;
                        }

                        $this->storageCleanup->delete($subjectUserId, [
                            'disk' => $attachment['disk'],
                            'path' => $attachment['path'],
                            'directory' => false,
                        ]);
                    }
                });
            }

            PartnerSupportAttachment::query()->whereIn('support_ticket_id', $ticketIds)->delete();
        }

        if (Schema::hasTable('partner_support_ticket_contexts')) {
            PartnerSupportTicketContext::query()->whereIn('support_ticket_id', $ticketIds)->delete();
        }

        SupportTicket::query()->whereIn('id', $ticketIds)->delete();
    }

    public function latestForUser(int $userId): ?PartnerOnboardingRequest
    {
        return PartnerOnboardingRequest::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->where('mlhub_user_id', $userId)
            ->orderByDesc('id')
            ->first();
    }

    private function integrationFor(PartnerOnboardingRequest $onboarding): ?PartnerIntegration
    {
        return PartnerIntegration::query()
            ->where('partner_code', $onboarding->partner_code)
            ->where('external_business_id', $onboarding->external_business_id)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    private function webhookPayload(PartnerOnboardingRequest $onboarding, PartnerIntegration $integration): array
    {
        return [
            'request_id' => $onboarding->request_id,
            'external_business_id' => $onboarding->external_business_id,
            'status' => $onboarding->status,
            'current_step' => $onboarding->current_step,
            'status_label' => $onboarding->statusLabel(),
            'requested_package_code' => $onboarding->requested_package_code,
            'package_code' => $integration->package_code,
            'approved_package_code' => $onboarding->approved_package_code,
            'mlhub_user_id' => $onboarding->mlhub_user_id,
            'mlhub_business_id' => $onboarding->mlhub_business_id,
            'assigned_consultant_id' => $onboarding->assigned_consultant_id,
            'review_note' => $onboarding->review_note,
            'occurred_at' => now()->utc()->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function safeAudit(string $event, string $description, array $attributes): void
    {
        if (! Schema::hasTable('audit_logs') || ! function_exists('log_activity')) {
            return;
        }

        try {
            log_activity($event, $description, $attributes);
        } catch (Throwable) {
            // Audit must not block admin actions.
        }
    }
}
