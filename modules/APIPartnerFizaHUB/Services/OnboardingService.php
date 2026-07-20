<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\User;
use Modules\AdminUser\Support\PersonalTeamProvisioner;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingStatusHistory;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Modules\APIPartnerFizaHUB\Support\PartnerApiException;
use Modules\AppAffiliate\Support\AffiliateService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Throwable;

class OnboardingService
{
    public function __construct(
        protected PartnerMappingService $mapping,
        protected PersonalTeamProvisioner $teams,
        protected AffiliateService $affiliate,
        protected SupportTicketBridge $supportTickets,
        protected PackageAssignmentService $packages,
        protected WebhookOutboxService $webhooks,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array{
     *     onboarding: PartnerOnboardingRequest,
     *     created: bool,
     *     account_created: bool,
     *     business_created: bool,
     *     integration_created: bool
     * }
     */
    public function upsert(array $payload, string $requestId): array
    {
        $partnerCode = $this->mapping->partnerCode();
        $externalBusinessId = $this->mapping->normalizeExternalId($payload['external_business_id'] ?? null);
        $externalUserId = $this->mapping->normalizeExternalId($payload['external_user_id'] ?? null);
        $requestedPackageCode = (string) (
            $payload['requested_package_code']
            ?? $payload['package_code']
            ?? $this->packages->defaultPackageCode()
        );
        $effectivePackageCode = $this->packages->defaultPackageCode();

        $owner = (array) ($payload['owner'] ?? []);
        $business = (array) ($payload['business'] ?? []);
        $verification = (array) ($payload['verification'] ?? []);

        $owner['email'] = $this->mapping->normalizeEmail($owner['email'] ?? null);
        $owner['phone'] = $this->mapping->normalizeIdentifier($owner['phone'] ?? null) ?? trim((string) ($owner['phone'] ?? ''));
        $business['phone'] = isset($business['phone'])
            ? ($this->mapping->normalizeIdentifier((string) $business['phone']) ?? trim((string) $business['phone']))
            : null;
        $business['email'] = isset($business['email']) && $business['email'] !== ''
            ? $this->mapping->normalizeEmail((string) $business['email'])
            : null;

        $normalizedTax = $this->mapping->normalizeIdentifier($business['tax_code'] ?? null);
        $normalizedLicense = $this->mapping->normalizeIdentifier($business['business_license_number'] ?? null);
        $identityVerified = (bool) ($verification['identity_verified'] ?? false);
        $verifiedAt = $this->mapping->normalizeVerifiedAt($verification['verified_at'] ?? null);

        $acceptedPayload = [
            'external_business_id' => $externalBusinessId,
            'external_user_id' => $externalUserId !== '' ? $externalUserId : null,
            'requested_package_code' => $requestedPackageCode,
            'package_code' => $requestedPackageCode,
            'owner' => [
                'name' => trim((string) ($owner['name'] ?? '')),
                'phone' => $owner['phone'],
                'email' => $owner['email'],
            ],
            'business' => [
                'name' => trim((string) ($business['name'] ?? '')),
                'industry' => trim((string) ($business['industry'] ?? '')),
                'phone' => $business['phone'],
                'email' => $business['email'],
                'website' => $business['website'] ?? null,
                'address' => $business['address'] ?? null,
                'tax_code' => isset($business['tax_code']) ? trim((string) $business['tax_code']) : null,
                'business_license_number' => isset($business['business_license_number'])
                    ? trim((string) $business['business_license_number'])
                    : null,
            ],
            'verification' => [
                'identity_verified' => $identityVerified,
                'verified_at' => $verifiedAt?->toIso8601String(),
                'verified_by' => $verification['verified_by'] ?? null,
            ],
            'metadata' => (array) ($payload['metadata'] ?? []),
        ];

        $verificationStatus = [
            'identity_verified' => $identityVerified,
            'verified_at' => $verifiedAt?->toIso8601String(),
            'verified_by' => $verification['verified_by'] ?? null,
        ];

        $result = DB::transaction(function () use (
            $partnerCode,
            $requestId,
            $externalBusinessId,
            $externalUserId,
            $requestedPackageCode,
            $effectivePackageCode,
            $acceptedPayload,
            $verificationStatus,
            $identityVerified,
            $verifiedAt,
            $normalizedTax,
            $normalizedLicense,
            $owner,
            $business,
        ): array {
            $onboarding = PartnerOnboardingRequest::query()
                ->where('request_id', $requestId)
                ->lockForUpdate()
                ->first();

            $created = false;
            $accountCreated = false;
            $businessCreated = false;
            $integrationCreated = false;

            if (! $onboarding) {
                $onboarding = PartnerOnboardingRequest::query()->create([
                    'partner_code' => $partnerCode,
                    'request_id' => $requestId,
                    'external_business_id' => $externalBusinessId,
                    'external_user_id' => $externalUserId !== '' ? $externalUserId : null,
                    'package_code' => $effectivePackageCode,
                    'requested_package_code' => $requestedPackageCode,
                    'approved_package_code' => null,
                    'status' => OnboardingStatusMachine::AWAITING_CONSULTANT,
                    'current_step' => OnboardingStatusMachine::defaultStepFor(OnboardingStatusMachine::AWAITING_CONSULTANT),
                    'admin_status' => OnboardingStatusMachine::AWAITING_CONSULTANT,
                    'payload' => $acceptedPayload,
                    'verification_status' => $verificationStatus,
                    'duplicate_check' => [],
                ]);
                $created = true;

                $this->recordHistory($onboarding, null, OnboardingStatusMachine::AWAITING_CONSULTANT, 'partner', null, 'Onboarding request accepted.');
            } else {
                $onboarding->forceFill([
                    'external_business_id' => $externalBusinessId,
                    'external_user_id' => $externalUserId !== '' ? $externalUserId : null,
                    'requested_package_code' => $requestedPackageCode,
                    'payload' => $acceptedPayload,
                    'verification_status' => $verificationStatus,
                ])->save();
            }

            $integration = PartnerIntegration::query()
                ->where('partner_code', $partnerCode)
                ->where('external_business_id', $externalBusinessId)
                ->lockForUpdate()
                ->first();

            if ($integration && $integration->mlhub_user_id && $integration->mlhub_business_id) {
                $this->updateExistingMapping(
                    $integration,
                    $onboarding,
                    $acceptedPayload,
                    $verificationStatus,
                    $normalizedTax,
                    $normalizedLicense,
                    $identityVerified,
                    $verifiedAt
                );

                $this->ensureOnboardingTicket($onboarding, $integration);

                return [
                    'onboarding' => $onboarding->fresh(['supportTicket']),
                    'created' => $created,
                    'account_created' => false,
                    'business_created' => false,
                    'integration_created' => false,
                ];
            }

            $duplicates = $this->mapping->detectDuplicates($externalBusinessId, $owner, $business);
            $emailConflict = collect($duplicates)->contains(fn (array $row): bool => ($row['type'] ?? '') === 'email');

            $provisionFlags = $this->provision(
                $onboarding,
                $acceptedPayload,
                $verificationStatus,
                $normalizedTax,
                $normalizedLicense,
                $identityVerified,
                $verifiedAt,
                $effectivePackageCode,
                $emailConflict,
                $duplicates
            );

            $accountCreated = $provisionFlags['account_created'];
            $businessCreated = $provisionFlags['business_created'];
            $integrationCreated = $provisionFlags['integration_created'];

            $integration = PartnerIntegration::query()
                ->where('partner_code', $partnerCode)
                ->where('external_business_id', $externalBusinessId)
                ->first();

            if ($integration) {
                $this->ensureOnboardingTicket($onboarding, $integration, $emailConflict || $duplicates !== []);
            }

            return [
                'onboarding' => $onboarding->fresh(['supportTicket']),
                'created' => $created,
                'account_created' => $accountCreated,
                'business_created' => $businessCreated,
                'integration_created' => $integrationCreated,
            ];
        });

        $onboarding = $result['onboarding'];
        $integration = PartnerIntegration::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->where('external_business_id', $onboarding->external_business_id)
            ->first();

        if ($integration) {
            $this->webhooks->queueOnboardingStatus(
                $this->webhookPayload($onboarding, $integration),
                $onboarding->request_id.'|'.$onboarding->status.'|provision'
            );
        }

        return $result;
    }

    public function find(string $requestId): PartnerOnboardingRequest
    {
        return PartnerOnboardingRequest::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->where('request_id', $requestId)
            ->firstOrFail();
    }

    public function confirm(string $requestId, ?string $note = null): PartnerOnboardingRequest
    {
        $onboarding = $this->find($requestId);

        $onboarding->forceFill([
            'partner_confirmed_at' => now(),
            'review_note' => $note ?: $onboarding->review_note,
        ])->save();

        $this->safeAudit('partner.fizahub.onboarding.confirm', 'Partner confirmed onboarding request.', [
            'subject_type' => PartnerOnboardingRequest::class,
            'subject_id' => $onboarding->id,
            'area' => 'admin',
            'metadata' => [
                'request_id' => $onboarding->request_id,
            ],
        ]);

        $integration = PartnerIntegration::query()
            ->where('partner_code', $onboarding->partner_code)
            ->where('external_business_id', $onboarding->external_business_id)
            ->first();

        if ($integration) {
            $this->webhooks->queueOnboardingStatus(
                $this->webhookPayload($onboarding->fresh(), $integration),
                $onboarding->request_id.'|partner_confirmed|'.now()->timestamp
            );
        }

        return $onboarding->fresh(['supportTicket']);
    }

    public function cancel(string $requestId, ?string $reason = null): PartnerOnboardingRequest
    {
        return app(OnboardingAdminService::class)->transition(
            $this->find($requestId),
            OnboardingStatusMachine::CANCELLED,
            'partner',
            null,
            $reason ?: 'Cancelled by partner request.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(
        PartnerOnboardingRequest $onboarding,
        bool $accountCreated = false,
        bool $businessCreated = false,
        bool $integrationCreated = false
    ): array {
        $ticketSecureId = null;

        if ($onboarding->support_ticket_id) {
            $ticketSecureId = $onboarding->supportTicket?->id_secure
                ?? SupportTicket::query()->whereKey($onboarding->support_ticket_id)->value('id_secure');
        }

        $integration = PartnerIntegration::query()
            ->where('partner_code', $onboarding->partner_code)
            ->where('external_business_id', $onboarding->external_business_id)
            ->first();

        $effectivePackage = $integration?->package_code
            ?: ($onboarding->package_code ?: $this->packages->defaultPackageCode());

        return [
            'request_id' => $onboarding->request_id,
            'external_business_id' => $onboarding->external_business_id,
            'account_created' => $accountCreated,
            'business_created' => $businessCreated,
            'integration_created' => $integrationCreated,
            'status' => $onboarding->status,
            'current_step' => $onboarding->current_step,
            'status_label' => $onboarding->statusLabel(),
            'requested_package_code' => $onboarding->requested_package_code,
            'approved_package_code' => $onboarding->approved_package_code,
            'package_code' => $effectivePackage,
            'duplicate_check' => $onboarding->duplicate_check ?? [],
            'support_ticket_id' => $ticketSecureId,
            'mlhub_user_id' => $onboarding->mlhub_user_id,
            'mlhub_workspace_id' => $onboarding->mlhub_workspace_id,
            'mlhub_business_id' => $onboarding->mlhub_business_id,
            'partner_confirmed_at' => optional($onboarding->partner_confirmed_at)?->utc()?->toIso8601String(),
            'assigned_consultant_id' => $onboarding->assigned_consultant_id,
            'last_synced_at' => optional($onboarding->last_synced_at)?->utc()?->toIso8601String(),
        ];
    }

    /**
     * @param  array<string, mixed>  $acceptedPayload
     * @param  array<string, mixed>  $verificationStatus
     */
    private function updateExistingMapping(
        PartnerIntegration $integration,
        PartnerOnboardingRequest $onboarding,
        array $acceptedPayload,
        array $verificationStatus,
        ?string $normalizedTax,
        ?string $normalizedLicense,
        bool $identityVerified,
        $verifiedAt
    ): void {
        $user = User::query()->findOrFail($integration->mlhub_user_id);
        $business = LocalBusiness::query()->findOrFail($integration->mlhub_business_id);

        $owner = (array) data_get($acceptedPayload, 'owner', []);
        $businessPayload = (array) data_get($acceptedPayload, 'business', []);
        $industry = $this->mapping->resolveIndustry((string) ($businessPayload['industry'] ?? ''));

        $user->forceFill([
            'name' => (string) ($owner['name'] ?? $user->name),
        ])->save();

        if ($identityVerified) {
            $user->forceFill([
                'email_verified_at' => $verifiedAt ?? now(),
            ])->save();
        }

        $business->forceFill([
            'name' => (string) ($businessPayload['name'] ?? $business->name),
            'phone' => $businessPayload['phone'] ?? $business->phone,
            'email' => $businessPayload['email'] ?? $business->email,
            'website' => $businessPayload['website'] ?? $business->website,
            'address' => $businessPayload['address'] ?? $business->address,
            'type' => $industry['legacy_type'],
            'industry_group_code' => $industry['group_code'],
            'industry_category_code' => $industry['category_code'],
            'industry_metadata' => $industry['metadata_snapshot'],
            'taxonomy_version' => $industry['taxonomy_version'],
        ])->save();

        $metadata = (array) ($integration->metadata ?? []);
        $metadata['tax_code'] = $normalizedTax;
        $metadata['business_license_number'] = $normalizedLicense;
        $metadata['source_industry'] = $businessPayload['industry'] ?? null;
        $metadata['resolved_industry_category'] = $industry['category_code'];
        $metadata['contact_email'] = $owner['email'] ?? ($metadata['contact_email'] ?? null);

        $integration->forceFill([
            'external_user_id' => $acceptedPayload['external_user_id'] ?? $integration->external_user_id,
            'status' => 'active',
            'verification_status' => $verificationStatus,
            'metadata' => $metadata,
        ])->save();

        $onboarding->forceFill([
            'requested_package_code' => $acceptedPayload['requested_package_code'] ?? $onboarding->requested_package_code,
            'package_code' => $integration->package_code ?: $onboarding->package_code,
            'duplicate_check' => [],
            'mlhub_user_id' => $integration->mlhub_user_id,
            'mlhub_workspace_id' => $integration->mlhub_workspace_id,
            'mlhub_business_id' => $integration->mlhub_business_id,
        ])->save();

        if (! in_array($onboarding->status, [
            OnboardingStatusMachine::READY,
            OnboardingStatusMachine::COMPLETED,
            OnboardingStatusMachine::CANCELLED,
        ], true)) {
            // Keep existing consulting workflow status; do not force completed on profile sync.
        }
    }

    /**
     * @param  array<string, mixed>  $acceptedPayload
     * @param  array<string, mixed>  $verificationStatus
     * @param  list<array{type: string, id: int}>  $duplicates
     * @return array{account_created: bool, business_created: bool, integration_created: bool}
     */
    private function provision(
        PartnerOnboardingRequest $onboarding,
        array $acceptedPayload,
        array $verificationStatus,
        ?string $normalizedTax,
        ?string $normalizedLicense,
        bool $identityVerified,
        $verifiedAt,
        string $effectivePackageCode,
        bool $emailConflict,
        array $duplicates
    ): array {
        $plan = $this->mapping->resolvePlan($effectivePackageCode);

        if (! $plan) {
            throw PartnerApiException::make(
                'default_plan_not_found',
                __('Hệ thống chưa sẵn sàng để tạo tài khoản. Vui lòng thử lại sau ít phút.'),
                503,
                ['next_action' => 'retry_later']
            );
        }

        $owner = (array) data_get($acceptedPayload, 'owner', []);
        $businessPayload = (array) data_get($acceptedPayload, 'business', []);
        $externalBusinessId = (string) $acceptedPayload['external_business_id'];
        $username = $this->mapping->availableUsername($externalBusinessId);
        $password = Str::password(64);
        $timezone = (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
        $contactEmail = (string) ($owner['email'] ?? '');
        $loginEmail = $contactEmail;
        $usesProvisionalEmail = false;

        if ($emailConflict) {
            $loginEmail = $this->mapping->provisionalEmail($externalBusinessId);
            $usesProvisionalEmail = true;
        }

        $user = User::query()->create([
            'name' => (string) ($owner['name'] ?? ''),
            'username' => $username,
            'email' => $loginEmail,
            'timezone' => $timezone,
            'locale' => 'vi',
            'plan_id' => $plan->id,
            'plan_started_at' => now(),
            'plan_expires_at' => null,
            'referral_code' => $this->affiliate->generateReferralCode($username),
            'password' => $password,
        ]);

        if ($identityVerified && ! $usesProvisionalEmail) {
            $user->forceFill([
                'email_verified_at' => $verifiedAt ?? now(),
            ])->save();
        }

        $this->affiliate->ensureProfile($user);
        $team = $this->teams->ensureForUser($user);

        $industry = $this->mapping->resolveIndustry((string) ($businessPayload['industry'] ?? ''));

        try {
            $business = LocalBusiness::query()->create([
                'user_id' => $user->id,
                'name' => (string) ($businessPayload['name'] ?? ''),
                'phone' => $businessPayload['phone'] ?? ($owner['phone'] ?? null),
                'email' => $businessPayload['email'] ?? ($usesProvisionalEmail ? $contactEmail : ($owner['email'] ?? null)),
                'website' => $businessPayload['website'] ?? null,
                'address' => $businessPayload['address'] ?? null,
                'type' => $industry['legacy_type'],
                'industry_group_code' => $industry['group_code'],
                'industry_category_code' => $industry['category_code'],
                'industry_metadata' => $industry['metadata_snapshot'],
                'taxonomy_version' => $industry['taxonomy_version'],
            ]);
        } catch (Throwable $exception) {
            throw $exception;
        }

        $status = $emailConflict || $duplicates !== []
            ? OnboardingStatusMachine::NEEDS_REVIEW
            : OnboardingStatusMachine::AWAITING_CONSULTANT;

        $integration = PartnerIntegration::query()->create([
            'partner_code' => $this->mapping->partnerCode(),
            'external_business_id' => $externalBusinessId,
            'external_user_id' => $acceptedPayload['external_user_id'] ?? null,
            'mlhub_user_id' => $user->id,
            'mlhub_workspace_id' => $team->id,
            'mlhub_business_id' => $business->id,
            'package_code' => $effectivePackageCode,
            'status' => 'active',
            'verification_status' => $verificationStatus,
            'metadata' => [
                'tax_code' => $normalizedTax,
                'business_license_number' => $normalizedLicense,
                'source_industry' => $businessPayload['industry'] ?? null,
                'resolved_industry_category' => $industry['category_code'],
                'contact_email' => $contactEmail,
                'uses_provisional_email' => $usesProvisionalEmail,
                'login_email' => $loginEmail,
            ],
        ]);

        $this->packages->assignEffectivePackage(
            $integration,
            $effectivePackageCode,
            null,
            'Initial Free package on FizaHUB provisioning.',
            $onboarding
        );

        $fromStatus = $onboarding->status;
        $onboarding->forceFill([
            'status' => $status,
            'current_step' => OnboardingStatusMachine::defaultStepFor($status),
            'admin_status' => $status,
            'package_code' => $effectivePackageCode,
            'requested_package_code' => $acceptedPayload['requested_package_code'] ?? $onboarding->requested_package_code,
            'duplicate_check' => $duplicates,
            'mlhub_user_id' => $user->id,
            'mlhub_workspace_id' => $team->id,
            'mlhub_business_id' => $business->id,
        ])->save();

        if ($fromStatus !== $status) {
            $this->recordHistory(
                $onboarding,
                $fromStatus,
                $status,
                'system',
                null,
                $emailConflict
                    ? 'Provisioned with provisional email due to duplicate contact email.'
                    : 'Account provisioned; awaiting consultant contact.'
            );
        }

        return [
            'account_created' => true,
            'business_created' => true,
            'integration_created' => true,
        ];
    }

    private function ensureOnboardingTicket(
        PartnerOnboardingRequest $onboarding,
        PartnerIntegration $integration,
        bool $needsReview = false
    ): void {
        if ($onboarding->support_ticket_id) {
            return;
        }

        $title = $needsReview
            ? 'FizaHUB onboarding needs review: '.$onboarding->external_business_id
            : 'FizaHUB onboarding awaiting consultant: '.$onboarding->external_business_id;

        $summary = $needsReview
            ? 'Account was provisioned with Free package but needs manual review (duplicate or conflict).'
            : 'Account provisioned with Free package. Consultant should contact the business owner.';

        $ticket = $this->supportTickets->createOnboardingReviewTicket(
            $onboarding,
            $title,
            $summary,
            (array) ($onboarding->duplicate_check ?? [])
        );

        if ($integration->mlhub_user_id) {
            $ticket->forceFill([
                'uid' => (int) $integration->mlhub_user_id,
                'open_by' => (int) $integration->mlhub_user_id,
                'team_id' => $integration->mlhub_workspace_id ? (int) $integration->mlhub_workspace_id : null,
            ])->save();
        }

        $onboarding->forceFill([
            'support_ticket_id' => $ticket->id,
        ])->save();
    }

    private function recordHistory(
        PartnerOnboardingRequest $onboarding,
        ?string $from,
        string $to,
        string $changedByType,
        ?int $changedById,
        ?string $reason,
        array $metadata = []
    ): void {
        if (! Schema::hasTable('partner_onboarding_status_histories')) {
            return;
        }

        PartnerOnboardingStatusHistory::query()->create([
            'onboarding_request_id' => $onboarding->id,
            'from_status' => $from,
            'to_status' => $to,
            'changed_by_type' => $changedByType,
            'changed_by_id' => $changedById,
            'reason' => $reason,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
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
            // Audit must not block onboarding.
        }
    }
}
