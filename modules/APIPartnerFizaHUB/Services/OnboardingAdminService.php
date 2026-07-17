<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingStatusHistory;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Throwable;

class OnboardingAdminService
{
    public function __construct(
        protected PartnerMappingService $mapping,
        protected WebhookOutboxService $webhooks,
        protected PackageAssignmentService $packages,
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
