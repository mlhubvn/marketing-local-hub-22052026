<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\User;
use Modules\AdminUser\Support\PersonalTeamProvisioner;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\AppAffiliate\Support\AffiliateService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use RuntimeException;
use Throwable;

class OnboardingService
{
    public function __construct(
        protected PartnerMappingService $mapping,
        protected PersonalTeamProvisioner $teams,
        protected AffiliateService $affiliate,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     * @return array{onboarding: PartnerOnboardingRequest, created: bool}
     */
    public function upsert(array $payload, string $requestId): array
    {
        $partnerCode = $this->mapping->partnerCode();
        $externalBusinessId = $this->mapping->normalizeExternalId($payload['external_business_id'] ?? null);
        $externalUserId = $this->mapping->normalizeExternalId($payload['external_user_id'] ?? null);
        $packageCode = (string) ($payload['package_code'] ?? config('modules.apipartnerfizahub.default_package', 'base'));

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
            'package_code' => $packageCode,
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
        ];

        $verificationStatus = [
            'identity_verified' => $identityVerified,
            'verified_at' => $verifiedAt?->toIso8601String(),
            'verified_by' => $verification['verified_by'] ?? null,
        ];

        return DB::transaction(function () use (
            $partnerCode,
            $requestId,
            $externalBusinessId,
            $externalUserId,
            $packageCode,
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

            if (! $onboarding) {
                $onboarding = PartnerOnboardingRequest::query()->create([
                    'partner_code' => $partnerCode,
                    'request_id' => $requestId,
                    'external_business_id' => $externalBusinessId,
                    'external_user_id' => $externalUserId !== '' ? $externalUserId : null,
                    'package_code' => $packageCode,
                    'status' => 'pending_verification',
                    'current_step' => 'verification',
                    'payload' => $acceptedPayload,
                    'verification_status' => $verificationStatus,
                    'duplicate_check' => [],
                ]);
                $created = true;
            } else {
                $onboarding->forceFill([
                    'external_business_id' => $externalBusinessId,
                    'external_user_id' => $externalUserId !== '' ? $externalUserId : null,
                    'package_code' => $packageCode,
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

                return ['onboarding' => $onboarding->fresh(['supportTicket']), 'created' => $created];
            }

            if (! $identityVerified) {
                $this->markNeedsAttention(
                    $onboarding,
                    'pending_verification',
                    'verification',
                    [],
                    'FizaHUB onboarding pending verification: '.$externalBusinessId,
                    'Identity is not verified yet. Manual review required before MLHUB provisioning.'
                );

                return ['onboarding' => $onboarding->fresh(['supportTicket']), 'created' => $created];
            }

            $duplicates = $this->mapping->detectDuplicates($externalBusinessId, $owner, $business);

            if ($duplicates !== []) {
                $this->markNeedsAttention(
                    $onboarding,
                    'needs_review',
                    'duplicate_review',
                    $duplicates,
                    'FizaHUB onboarding duplicate review: '.$externalBusinessId,
                    'Possible duplicate detected. Do not auto-attach to an existing MLHUB account.'
                );

                return ['onboarding' => $onboarding->fresh(['supportTicket']), 'created' => $created];
            }

            $this->provision(
                $onboarding,
                $acceptedPayload,
                $verificationStatus,
                $normalizedTax,
                $normalizedLicense,
                $identityVerified,
                $verifiedAt
            );

            return ['onboarding' => $onboarding->fresh(['supportTicket']), 'created' => $created];
        });
    }

    public function find(string $requestId): PartnerOnboardingRequest
    {
        return PartnerOnboardingRequest::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->where('request_id', $requestId)
            ->firstOrFail();
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(PartnerOnboardingRequest $onboarding): array
    {
        $ticketSecureId = null;

        if ($onboarding->support_ticket_id) {
            $ticketSecureId = $onboarding->supportTicket?->id_secure
                ?? SupportTicket::query()->whereKey($onboarding->support_ticket_id)->value('id_secure');
        }

        return [
            'request_id' => $onboarding->request_id,
            'external_business_id' => $onboarding->external_business_id,
            'package_code' => $onboarding->package_code,
            'status' => $onboarding->status,
            'current_step' => $onboarding->current_step,
            'duplicate_check' => $onboarding->duplicate_check ?? [],
            'support_ticket_id' => $ticketSecureId,
            'mlhub_user_id' => $onboarding->mlhub_user_id,
            'mlhub_workspace_id' => $onboarding->mlhub_workspace_id,
            'mlhub_business_id' => $onboarding->mlhub_business_id,
        ];
    }

    /**
     * @param  array<string, mixed>  $acceptedPayload
     * @param  array<string, mixed>  $verificationStatus
     * @param  list<array{type: string, id: int}>  $duplicates
     */
    private function markNeedsAttention(
        PartnerOnboardingRequest $onboarding,
        string $status,
        string $currentStep,
        array $duplicates,
        string $title,
        string $summary
    ): void {
        $onboarding->forceFill([
            'status' => $status,
            'current_step' => $currentStep,
            'duplicate_check' => $duplicates,
            'mlhub_user_id' => null,
            'mlhub_workspace_id' => null,
            'mlhub_business_id' => null,
        ])->save();

        $ticket = $this->ensureReviewTicket($onboarding, $title, $summary, $duplicates);

        $onboarding->forceFill([
            'support_ticket_id' => $ticket->id,
        ])->save();
    }

    /**
     * @param  list<array{type: string, id: int}>  $duplicates
     */
    private function ensureReviewTicket(
        PartnerOnboardingRequest $onboarding,
        string $title,
        string $summary,
        array $duplicates
    ): SupportTicket {
        if ($onboarding->support_ticket_id) {
            $existing = SupportTicket::query()->find($onboarding->support_ticket_id);

            if ($existing) {
                $existing->forceFill([
                    'title' => $title,
                    'content' => $this->ticketBody($onboarding, $summary, $duplicates),
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
            'uid' => 0,
            'open_by' => 0,
            'team_id' => null,
            'cate_id' => null,
            'type_id' => null,
            'title' => $title,
            'content' => $this->ticketBody($onboarding, $summary, $duplicates),
            'status' => 1,
            'pin' => false,
            'user_read' => false,
            'admin_read' => true,
            'created' => time(),
            'changed' => time(),
        ]);
    }

    /**
     * @param  list<array{type: string, id: int}>  $duplicates
     */
    private function ticketBody(
        PartnerOnboardingRequest $onboarding,
        string $summary,
        array $duplicates
    ): string {
        $payload = [
            'summary' => $summary,
            'request_id' => $onboarding->request_id,
            'external_business_id' => $onboarding->external_business_id,
            'package_code' => $onboarding->package_code,
            'status' => $onboarding->status,
            'verification_status' => $onboarding->verification_status,
            'duplicate_check' => $duplicates,
        ];

        return json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: $summary;
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

        if ($identityVerified && $verifiedAt) {
            // Upstream identity verification from FizaHUB — not MLHUB email-link verification.
            $user->forceFill([
                'email_verified_at' => $verifiedAt,
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

        $integration->forceFill([
            'external_user_id' => $acceptedPayload['external_user_id'] ?? $integration->external_user_id,
            'package_code' => $acceptedPayload['package_code'] ?? $integration->package_code,
            'status' => 'active',
            'verification_status' => $verificationStatus,
            'metadata' => $metadata,
        ])->save();

        $onboarding->forceFill([
            'status' => 'completed',
            'current_step' => 'ready',
            'duplicate_check' => [],
            'mlhub_user_id' => $integration->mlhub_user_id,
            'mlhub_workspace_id' => $integration->mlhub_workspace_id,
            'mlhub_business_id' => $integration->mlhub_business_id,
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $acceptedPayload
     * @param  array<string, mixed>  $verificationStatus
     */
    private function provision(
        PartnerOnboardingRequest $onboarding,
        array $acceptedPayload,
        array $verificationStatus,
        ?string $normalizedTax,
        ?string $normalizedLicense,
        bool $identityVerified,
        $verifiedAt
    ): void {
        $plan = $this->mapping->resolvePlan((string) $acceptedPayload['package_code']);

        if (! $plan) {
            throw new RuntimeException('Configured FizaHUB package plan was not found.');
        }

        $owner = (array) data_get($acceptedPayload, 'owner', []);
        $businessPayload = (array) data_get($acceptedPayload, 'business', []);
        $externalBusinessId = (string) $acceptedPayload['external_business_id'];
        $username = $this->mapping->deterministicUsername($externalBusinessId);
        $password = Str::password(64);
        $timezone = (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');

        $user = User::query()->create([
            'name' => (string) ($owner['name'] ?? ''),
            'username' => $username,
            'email' => (string) ($owner['email'] ?? ''),
            'timezone' => $timezone,
            'locale' => 'vi',
            'plan_id' => $plan->id,
            'plan_started_at' => now(),
            'plan_expires_at' => null,
            'referral_code' => $this->affiliate->generateReferralCode($username),
            'password' => $password,
        ]);

        if ($identityVerified && $verifiedAt) {
            // Upstream identity verification from FizaHUB — not MLHUB email-link verification.
            $user->forceFill([
                'email_verified_at' => $verifiedAt,
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
                'email' => $businessPayload['email'] ?? ($owner['email'] ?? null),
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

        $integration = PartnerIntegration::query()->create([
            'partner_code' => $this->mapping->partnerCode(),
            'external_business_id' => $externalBusinessId,
            'external_user_id' => $acceptedPayload['external_user_id'] ?? null,
            'mlhub_user_id' => $user->id,
            'mlhub_workspace_id' => $team->id,
            'mlhub_business_id' => $business->id,
            'package_code' => (string) $acceptedPayload['package_code'],
            'status' => 'active',
            'verification_status' => $verificationStatus,
            'metadata' => [
                'tax_code' => $normalizedTax,
                'business_license_number' => $normalizedLicense,
                'source_industry' => $businessPayload['industry'] ?? null,
                'resolved_industry_category' => $industry['category_code'],
            ],
        ]);

        $onboarding->forceFill([
            'status' => 'completed',
            'current_step' => 'ready',
            'duplicate_check' => [],
            'support_ticket_id' => $onboarding->support_ticket_id,
            'mlhub_user_id' => $user->id,
            'mlhub_workspace_id' => $team->id,
            'mlhub_business_id' => $business->id,
        ])->save();

        unset($integration);
    }
}
