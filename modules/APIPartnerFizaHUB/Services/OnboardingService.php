<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\Team;
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
        protected PartnerIdentityService $identity,
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
     *     already_registered: bool,
     *     account_created: bool,
     *     business_created: bool,
     *     integration_created: bool
     * }
     */
    public function upsert(array $payload, string $requestId): array
    {
        $acceptedPayload = $this->normalizePayload($payload);
        $partnerCode = $this->mapping->partnerCode();
        $externalBusinessId = (string) $acceptedPayload['external_business_id'];
        $ownerEmail = (string) data_get($acceptedPayload, 'owner.email');

        $result = null;

        for ($attempt = 0; $attempt < 2; $attempt++) {
            try {
                $result = DB::transaction(function () use (
                    $acceptedPayload,
                    $partnerCode,
                    $externalBusinessId,
                    $ownerEmail,
                    $requestId
                ): array {
                    $integration = PartnerIntegration::query()
                        ->where('partner_code', $partnerCode)
                        ->where('external_business_id', $externalBusinessId)
                        ->lockForUpdate()
                        ->first();

                    if ($integration) {
                        return $this->reuseExistingRegistration($integration, $acceptedPayload);
                    }

                    if (User::query()->where('email', $ownerEmail)->lockForUpdate()->exists()) {
                        throw PartnerApiException::make(
                            'email_already_registered',
                            __('Địa chỉ email này đã được đăng ký trên MLHUB.'),
                            409,
                            ['next_action' => 'use_existing_account_or_contact_support']
                        );
                    }

                    return $this->provisionNewRegistration($acceptedPayload, $requestId);
                });

                break;
            } catch (QueryException $exception) {
                if ($attempt > 0 || ! $this->isRetryableIdentityRace($exception)) {
                    throw $exception;
                }
            }
        }

        if (! is_array($result)) {
            throw new \LogicException('Onboarding transaction did not return a result.');
        }

        $onboarding = $result['onboarding'];
        $integration = PartnerIntegration::query()
            ->where('partner_code', $partnerCode)
            ->where('external_business_id', $onboarding->external_business_id)
            ->first();

        if ($integration) {
            $this->webhooks->queueOnboardingStatus(
                $this->webhookPayload($onboarding, $integration),
                $onboarding->request_id.'|'.$onboarding->publicStatus().'|provision'
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
            'metadata' => ['request_id' => $onboarding->request_id],
        ]);

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

    /** @return array<string, mixed> */
    public function serialize(
        PartnerOnboardingRequest $onboarding,
        bool $accountCreated = false,
        bool $businessCreated = false,
        bool $integrationCreated = false,
        bool $alreadyRegistered = false
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
        $user = $onboarding->mlhub_user_id
            ? User::query()->find($onboarding->mlhub_user_id)
            : null;
        $effectivePackage = $integration?->package_code
            ?: ($onboarding->package_code ?: $this->packages->defaultPackageCode());
        $publicStatus = $onboarding->publicStatus();

        return [
            'request_id' => $onboarding->request_id,
            'external_business_id' => $onboarding->external_business_id,
            'external_user_id' => $onboarding->external_user_id,
            'username' => $user?->username,
            'account_created' => $accountCreated,
            'business_created' => $businessCreated,
            'integration_created' => $integrationCreated,
            'already_registered' => $alreadyRegistered,
            'status' => $publicStatus,
            'current_step' => OnboardingStatusMachine::defaultStepFor($publicStatus),
            'status_label' => OnboardingStatusMachine::label($publicStatus),
            'timeline' => $this->timeline($onboarding),
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

    /** @param array<string, mixed> $payload */
    private function normalizePayload(array $payload): array
    {
        $owner = (array) ($payload['owner'] ?? []);
        $business = (array) ($payload['business'] ?? []);
        $verification = (array) ($payload['verification'] ?? []);
        $requestedPackageCode = (string) (
            $payload['requested_package_code']
            ?? $payload['package_code']
            ?? $this->packages->defaultPackageCode()
        );
        $verifiedAt = $this->mapping->normalizeVerifiedAt($verification['verified_at'] ?? null);

        return [
            'external_business_id' => $this->mapping->normalizeExternalId($payload['external_business_id'] ?? null),
            'external_user_id' => $this->nullableExternalId($payload['external_user_id'] ?? null),
            'marketing_goal_codes' => array_values(array_map(
                static fn (mixed $code): string => trim((string) $code),
                (array) ($payload['marketing_goal_codes'] ?? [])
            )),
            'requested_package_code' => $requestedPackageCode,
            'package_code' => $requestedPackageCode,
            'owner' => [
                'name' => trim((string) ($owner['name'] ?? '')),
                'phone' => $this->mapping->normalizeIdentifier($owner['phone'] ?? null),
                'email' => $this->mapping->normalizeEmail($owner['email'] ?? null),
            ],
            'business' => [
                'name' => trim((string) ($business['name'] ?? '')),
                'industry' => trim((string) ($business['industry'] ?? '')),
                'phone' => $this->mapping->normalizeIdentifier($business['phone'] ?? null),
                'email' => $this->mapping->normalizeEmail($business['email'] ?? null),
                'website' => $this->nullableTrimmed($business['website'] ?? null),
                'address' => trim((string) ($business['address'] ?? '')),
                'tax_code' => $this->nullableTrimmed($business['tax_code'] ?? null),
                'business_license_number' => $this->nullableTrimmed($business['business_license_number'] ?? null),
            ],
            'verification' => [
                'identity_verified' => (bool) ($verification['identity_verified'] ?? false),
                'verified_at' => $verifiedAt?->toIso8601String(),
                'verified_by' => $this->nullableTrimmed($verification['verified_by'] ?? null),
            ],
            'metadata' => (array) ($payload['metadata'] ?? []),
        ];
    }

    /** @param array<string, mixed> $acceptedPayload */
    private function reuseExistingRegistration(
        PartnerIntegration $integration,
        array $acceptedPayload
    ): array {
        $user = $integration->mlhub_user_id
            ? User::query()->lockForUpdate()->find($integration->mlhub_user_id)
            : null;
        $business = $integration->mlhub_business_id
            ? LocalBusiness::query()->lockForUpdate()->find($integration->mlhub_business_id)
            : null;
        $teamExists = $integration->mlhub_workspace_id
            && Team::query()->whereKey($integration->mlhub_workspace_id)->exists();

        if (! $user || ! $business || ! $teamExists) {
            throw PartnerApiException::make(
                'integration_broken',
                __('Liên kết MLHUB không hợp lệ.'),
                409,
                ['next_action' => 'contact_support']
            );
        }

        $ownerEmail = (string) data_get($acceptedPayload, 'owner.email');

        if (! hash_equals(Str::lower((string) $user->email), Str::lower($ownerEmail))) {
            throw PartnerApiException::make(
                'onboarding_email_mismatch',
                __('Email đăng ký không khớp với tài khoản đã liên kết trước đó.'),
                409
            );
        }

        $onboarding = PartnerOnboardingRequest::query()
            ->where('partner_code', $integration->partner_code)
            ->where('external_business_id', $integration->external_business_id)
            ->latest('id')
            ->lockForUpdate()
            ->first();

        if (! $onboarding) {
            throw PartnerApiException::make(
                'integration_broken',
                __('Liên kết MLHUB chưa có hồ sơ onboarding chuẩn.'),
                409,
                ['next_action' => 'contact_support']
            );
        }

        $this->updateExistingProfile($user, $business, $acceptedPayload);

        $storedPayload = (array) ($onboarding->payload ?? []);
        data_set($storedPayload, 'owner.name', data_get($acceptedPayload, 'owner.name'));
        data_set($storedPayload, 'business', data_get($acceptedPayload, 'business', []));

        $onboarding->forceFill([
            'payload' => $storedPayload,
            'mlhub_user_id' => $integration->mlhub_user_id,
            'mlhub_workspace_id' => $integration->mlhub_workspace_id,
            'mlhub_business_id' => $integration->mlhub_business_id,
        ])->save();

        $this->ensureOnboardingTicket($onboarding, $integration);

        return [
            'onboarding' => $onboarding->fresh(['supportTicket']),
            'created' => false,
            'already_registered' => true,
            'account_created' => false,
            'business_created' => false,
            'integration_created' => false,
        ];
    }

    /** @param array<string, mixed> $acceptedPayload */
    private function provisionNewRegistration(array $acceptedPayload, string $requestId): array
    {
        $effectivePackageCode = $this->packages->defaultPackageCode();
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
        $verification = (array) data_get($acceptedPayload, 'verification', []);
        $username = $this->identity->availableUsernameFromEmail((string) $owner['email']);

        $user = User::query()->create([
            'name' => (string) $owner['name'],
            'username' => $username,
            'email' => (string) $owner['email'],
            'timezone' => (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh'),
            'locale' => 'vi',
            'plan_id' => $plan->id,
            'plan_started_at' => now(),
            'plan_expires_at' => null,
            'referral_code' => $this->affiliate->generateReferralCode($username),
            'password' => Str::password(64),
        ]);

        if ((bool) ($verification['identity_verified'] ?? false)) {
            $user->forceFill([
                'email_verified_at' => data_get($verification, 'verified_at') ?: now(),
            ])->save();
        }

        $this->affiliate->ensureProfile($user);
        $team = $this->teams->ensureForUser($user);
        $industry = $this->mapping->resolveIndustry((string) $businessPayload['industry']);

        $business = LocalBusiness::query()->create([
            'user_id' => $user->id,
            'name' => (string) $businessPayload['name'],
            'phone' => $businessPayload['phone'],
            'email' => $businessPayload['email'],
            'website' => $businessPayload['website'],
            'address' => $businessPayload['address'],
            'type' => $industry['legacy_type'],
            'industry_group_code' => $industry['group_code'],
            'industry_category_code' => $industry['category_code'],
            'industry_metadata' => $industry['metadata_snapshot'],
            'taxonomy_version' => $industry['taxonomy_version'],
        ]);

        $duplicates = $this->reviewDuplicates($acceptedPayload);
        $status = $duplicates === []
            ? OnboardingStatusMachine::AWAITING_CONSULTANT
            : OnboardingStatusMachine::NEEDS_REVIEW;
        $verificationStatus = (array) $verification;
        $integration = PartnerIntegration::query()->create([
            'partner_code' => $this->mapping->partnerCode(),
            'external_business_id' => $acceptedPayload['external_business_id'],
            'external_user_id' => $acceptedPayload['external_user_id'],
            'mlhub_user_id' => $user->id,
            'mlhub_workspace_id' => $team->id,
            'mlhub_business_id' => $business->id,
            'package_code' => $effectivePackageCode,
            'status' => 'active',
            'verification_status' => $verificationStatus,
            'metadata' => [
                'tax_code' => $this->mapping->normalizeIdentifier($businessPayload['tax_code'] ?? null),
                'business_license_number' => $this->mapping->normalizeIdentifier($businessPayload['business_license_number'] ?? null),
                'source_industry' => $businessPayload['industry'],
                'resolved_industry_category' => $industry['category_code'],
                'contact_email' => $owner['email'],
                'login_email' => $owner['email'],
                'uses_provisional_email' => false,
                'marketing_goal_codes' => $acceptedPayload['marketing_goal_codes'],
            ],
        ]);

        $this->packages->assignEffectivePackage(
            $integration,
            $effectivePackageCode,
            null,
            'Initial Free package on FizaHUB provisioning.'
        );

        $onboarding = PartnerOnboardingRequest::query()->create([
            'partner_code' => $this->mapping->partnerCode(),
            'request_id' => $requestId,
            'external_business_id' => $acceptedPayload['external_business_id'],
            'external_user_id' => $acceptedPayload['external_user_id'],
            'package_code' => $effectivePackageCode,
            'requested_package_code' => $acceptedPayload['requested_package_code'],
            'approved_package_code' => null,
            'status' => $status,
            'current_step' => OnboardingStatusMachine::defaultStepFor($status),
            'admin_status' => $status,
            'payload' => $acceptedPayload,
            'verification_status' => $verificationStatus,
            'duplicate_check' => $duplicates,
            'mlhub_user_id' => $user->id,
            'mlhub_workspace_id' => $team->id,
            'mlhub_business_id' => $business->id,
        ]);

        $this->recordHistory(
            $onboarding,
            null,
            $status,
            'partner',
            null,
            $status === OnboardingStatusMachine::NEEDS_REVIEW
                ? 'Onboarding requires manual review.'
                : 'Account provisioned; awaiting consultant contact.'
        );
        $this->ensureOnboardingTicket($onboarding, $integration, $status === OnboardingStatusMachine::NEEDS_REVIEW);

        return [
            'onboarding' => $onboarding->fresh(['supportTicket']),
            'created' => true,
            'already_registered' => false,
            'account_created' => true,
            'business_created' => true,
            'integration_created' => true,
        ];
    }

    /** @param array<string, mixed> $acceptedPayload */
    private function updateExistingProfile(User $user, LocalBusiness $business, array $acceptedPayload): void
    {
        $owner = (array) data_get($acceptedPayload, 'owner', []);
        $businessPayload = (array) data_get($acceptedPayload, 'business', []);
        $industry = $this->mapping->resolveIndustry((string) $businessPayload['industry']);

        $user->forceFill(['name' => (string) $owner['name']])->save();
        $business->forceFill([
            'name' => (string) $businessPayload['name'],
            'phone' => $businessPayload['phone'],
            'email' => $businessPayload['email'],
            'website' => $businessPayload['website'],
            'address' => $businessPayload['address'],
            'type' => $industry['legacy_type'],
            'industry_group_code' => $industry['group_code'],
            'industry_category_code' => $industry['category_code'],
            'industry_metadata' => $industry['metadata_snapshot'],
            'taxonomy_version' => $industry['taxonomy_version'],
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $acceptedPayload
     * @return list<array{type: string, id: int}>
     */
    private function reviewDuplicates(array $acceptedPayload): array
    {
        return array_values(array_filter(
            $this->mapping->detectDuplicates(
                (string) $acceptedPayload['external_business_id'],
                (array) $acceptedPayload['owner'],
                (array) $acceptedPayload['business']
            ),
            static fn (array $duplicate): bool => in_array(
                $duplicate['type'] ?? null,
                ['tax_code', 'business_license_number'],
                true
            )
        ));
    }

    private function ensureOnboardingTicket(
        PartnerOnboardingRequest $onboarding,
        PartnerIntegration $integration,
        bool $needsReview = false
    ): void {
        $payload = (array) ($onboarding->payload ?? []);
        $ticketWasCreated = (bool) data_get($payload, '_system.onboarding_ticket_created', false);
        $ticketExists = $onboarding->support_ticket_id
            && SupportTicket::query()->whereKey($onboarding->support_ticket_id)->exists();
        $auditReason = $ticketWasCreated && ! $ticketExists
            ? 'referenced_onboarding_ticket_missing'
            : null;
        $title = $needsReview
            ? 'FizaHUB onboarding needs review: '.$onboarding->external_business_id
            : 'FizaHUB onboarding awaiting consultant: '.$onboarding->external_business_id;
        $summary = $needsReview
            ? 'Onboarding needs manual review before consultation.'
            : 'Account provisioned with Free package. Consultant should contact the business owner.';

        $ticket = $this->supportTickets->createOnboardingReviewTicket(
            $onboarding,
            $integration,
            $title,
            $summary,
            (array) ($onboarding->duplicate_check ?? []),
            $auditReason
        );

        data_set($payload, '_system.onboarding_ticket_created', true);
        $onboarding->forceFill([
            'support_ticket_id' => $ticket->id,
            'payload' => $payload,
        ])->save();
    }

    /** @return list<array<string, mixed>> */
    private function timeline(PartnerOnboardingRequest $onboarding): array
    {
        $steps = [
            ['code' => 'account_created', 'label' => 'Đã tạo tài khoản và cơ sở', 'description' => 'Tài khoản MLHUB và cơ sở đã được khởi tạo.'],
            ['code' => OnboardingStatusMachine::AWAITING_CONSULTANT, 'label' => 'Chờ tư vấn viên liên hệ', 'description' => 'MLHUB đang tiếp nhận và kiểm tra yêu cầu.'],
            ['code' => OnboardingStatusMachine::IN_CONSULTATION, 'label' => 'Đang tư vấn nhu cầu', 'description' => 'Tư vấn viên đang làm rõ mục tiêu tăng trưởng.'],
            ['code' => OnboardingStatusMachine::CONFIGURING, 'label' => 'Đang cấu hình Marketing', 'description' => 'MLHUB đang cấu hình các công cụ Marketing.'],
            ['code' => OnboardingStatusMachine::READY, 'label' => 'Sẵn sàng sử dụng', 'description' => 'Doanh nghiệp có thể truy cập CRM MLHUB.'],
        ];
        $publicStatus = $onboarding->publicStatus();
        $currentIndex = match ($publicStatus) {
            OnboardingStatusMachine::IN_CONSULTATION => 2,
            OnboardingStatusMachine::CONFIGURING => 3,
            OnboardingStatusMachine::READY, OnboardingStatusMachine::COMPLETED => 4,
            default => 1,
        };

        return array_map(function (array $step, int $index) use ($onboarding, $publicStatus, $currentIndex): array {
            if ($publicStatus === OnboardingStatusMachine::NEEDS_REVIEW) {
                $status = $index === 0 ? 'completed' : ($index === 1 ? 'blocked' : 'pending');
            } elseif ($publicStatus === OnboardingStatusMachine::CANCELLED) {
                $status = $index === 0 ? 'completed' : ($index === $currentIndex ? 'blocked' : 'pending');
            } elseif ($publicStatus === OnboardingStatusMachine::COMPLETED || $index < $currentIndex) {
                $status = 'completed';
            } elseif ($index === $currentIndex) {
                $status = 'current';
            } else {
                $status = 'pending';
            }

            return $step + [
                'status' => $status,
                'completed_at' => $status === 'completed'
                    ? $this->completedAtFor($onboarding, (string) $step['code'])
                    : null,
            ];
        }, $steps, array_keys($steps));
    }

    private function completedAtFor(PartnerOnboardingRequest $onboarding, string $code): ?string
    {
        $date = match ($code) {
            'account_created' => $onboarding->created_at,
            OnboardingStatusMachine::AWAITING_CONSULTANT,
            OnboardingStatusMachine::IN_CONSULTATION => $onboarding->consultant_contacted_at ?? $onboarding->created_at,
            OnboardingStatusMachine::CONFIGURING => $onboarding->ready_at ?? $onboarding->updated_at,
            OnboardingStatusMachine::READY => $onboarding->completed_at ?? $onboarding->ready_at,
            default => null,
        };

        return optional($date)?->utc()?->toIso8601String();
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

    /** @return array<string, mixed> */
    private function webhookPayload(PartnerOnboardingRequest $onboarding, PartnerIntegration $integration): array
    {
        return [
            'request_id' => $onboarding->request_id,
            'external_business_id' => $onboarding->external_business_id,
            'status' => $onboarding->publicStatus(),
            'current_step' => OnboardingStatusMachine::defaultStepFor($onboarding->publicStatus()),
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

    private function nullableExternalId(mixed $value): ?string
    {
        $normalized = $this->mapping->normalizeExternalId(is_string($value) ? $value : null);

        return $normalized !== '' ? $normalized : null;
    }

    private function nullableTrimmed(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function isRetryableIdentityRace(QueryException $exception): bool
    {
        $message = Str::lower($exception->getMessage());

        return collect([
            'partner_integrations_partner_external_business_unique',
            'users_email_unique',
            'users_username_unique',
            'partner_integrations.partner_code, partner_integrations.external_business_id',
            'users.email',
            'users.username',
        ])->contains(fn (string $constraint): bool => str_contains($message, $constraint));
    }

    /** @param array<string, mixed> $attributes */
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
