<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class IntegrationProfileService
{
    public function __construct(
        protected PartnerMappingService $mapping
    ) {}

    public function findIntegration(string $externalBusinessId): PartnerIntegration
    {
        $integration = PartnerIntegration::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->where('external_business_id', $this->mapping->normalizeExternalId($externalBusinessId))
            ->first();

        if (! $integration) {
            throw (new ModelNotFoundException)->setModel(PartnerIntegration::class, [$externalBusinessId]);
        }

        return $integration;
    }

    /**
     * @return array<string, mixed>
     */
    public function status(string $externalBusinessId): array
    {
        return $this->marketingStatus($externalBusinessId);
    }

    /** @return array<string, mixed> */
    public function marketingStatus(string $externalBusinessId): array
    {
        $integration = $this->findIntegration($externalBusinessId);
        $onboarding = $this->latestOnboarding($integration->external_business_id);
        $onboardingStatus = $onboarding?->publicStatus();
        $isReady = in_array($onboardingStatus, ['ready', 'completed'], true);
        $isActive = $integration->status === 'active' && $isReady;
        $ticketSecureId = $onboarding?->supportTicket?->id_secure;

        return [
            'external_business_id' => $integration->external_business_id,
            'external_user_id' => $integration->external_user_id,
            'activation_status' => $this->activationStatus($integration, $onboarding),
            'onboarding_status' => $onboardingStatus,
            'is_ready' => $isReady,
            'effective_package_code' => $integration->package_code,
            'requested_package_code' => $onboarding?->requested_package_code,
            'onboarding_request_id' => $onboarding?->request_id,
            'support_ticket_id' => $ticketSecureId,
            'mlhub_user_id' => $integration->mlhub_user_id,
            'mlhub_workspace_id' => $integration->mlhub_workspace_id,
            'mlhub_business_id' => $integration->mlhub_business_id,
            'capabilities' => [
                'dashboard' => $isActive,
                'support' => true,
                'crm' => $isReady,
            ],
            'links' => $this->linksFor($integration, $onboarding, $ticketSecureId),
        ];
    }

    /** @param array<string, mixed> $input */
    public function updatePreferences(string $externalBusinessId, array $input): array
    {
        $integration = $this->findIntegration($externalBusinessId);

        return DB::transaction(function () use ($integration, $input): array {
            $goals = array_values((array) $input['marketing_goal_codes']);
            $requestedPackage = (string) $input['requested_package_code'];
            $metadata = (array) ($integration->metadata ?? []);
            $metadata['marketing_goal_codes'] = $goals;
            $metadata['requested_package_code'] = $requestedPackage;

            $integration->forceFill(['metadata' => $metadata])->save();

            $onboarding = $this->latestOnboarding($integration->external_business_id);

            if ($onboarding) {
                $payload = (array) ($onboarding->payload ?? []);
                $payload['marketing_goal_codes'] = $goals;
                $payload['requested_package_code'] = $requestedPackage;
                $onboarding->forceFill([
                    'requested_package_code' => $requestedPackage,
                    'payload' => $payload,
                ])->save();
            }

            return [
                'external_business_id' => $integration->external_business_id,
                'marketing_goal_codes' => $goals,
                'requested_package_code' => $requestedPackage,
                'effective_package_code' => $integration->package_code,
            ];
        });
    }

    /**
     * Update allowed owner/business profile fields on the mapped user, business and integration metadata.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function updateProfile(string $externalBusinessId, array $input): array
    {
        $integration = $this->findIntegration($externalBusinessId);

        if (! $integration->mlhub_user_id || ! $integration->mlhub_business_id) {
            throw (new ModelNotFoundException)->setModel(PartnerIntegration::class, [$externalBusinessId]);
        }

        DB::transaction(function () use ($integration, $input): void {
            $owner = (array) ($input['owner'] ?? []);
            $business = (array) ($input['business'] ?? []);

            $user = User::query()
                ->whereKey($integration->mlhub_user_id)
                ->firstOrFail();

            if (array_key_exists('name', $owner) && trim((string) $owner['name']) !== '') {
                $user->forceFill(['name' => trim((string) $owner['name'])])->save();
            }

            $localBusiness = LocalBusiness::query()
                ->where('user_id', $integration->mlhub_user_id)
                ->whereKey($integration->mlhub_business_id)
                ->firstOrFail();

            $businessUpdates = [];

            foreach (['name', 'phone', 'email', 'website', 'address'] as $field) {
                if (array_key_exists($field, $business)) {
                    $businessUpdates[$field] = $business[$field] !== '' ? $business[$field] : null;
                }
            }

            if (array_key_exists('industry', $business) && trim((string) $business['industry']) !== '') {
                $industry = $this->mapping->resolveIndustry((string) $business['industry']);
                $businessUpdates += [
                    'type' => $industry['legacy_type'],
                    'industry_group_code' => $industry['group_code'],
                    'industry_category_code' => $industry['category_code'],
                    'industry_metadata' => $industry['metadata_snapshot'],
                    'taxonomy_version' => $industry['taxonomy_version'],
                ];
            }

            if ($businessUpdates !== []) {
                $localBusiness->forceFill($businessUpdates)->save();
            }

        });

        return $this->status($integration->external_business_id);
    }

    private function latestOnboarding(string $externalBusinessId): ?PartnerOnboardingRequest
    {
        return PartnerOnboardingRequest::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->where('external_business_id', $externalBusinessId)
            ->orderByDesc('id')
            ->first();
    }

    private function activationStatus(
        PartnerIntegration $integration,
        ?PartnerOnboardingRequest $onboarding
    ): string {
        if ($integration->status !== 'active') {
            return 'suspended';
        }

        return in_array($onboarding?->publicStatus(), ['ready', 'completed'], true)
            ? 'active'
            : 'onboarding';
    }

    /** @return array<string, string|null> */
    private function linksFor(
        PartnerIntegration $integration,
        ?PartnerOnboardingRequest $onboarding,
        ?string $ticketSecureId
    ): array {
        $base = '/api/v1/partners/fizahub/businesses/'.$integration->external_business_id;

        return [
            'onboarding' => $onboarding
                ? '/api/v1/partners/fizahub/onboarding-requests/'.$onboarding->request_id
                : null,
            'dashboard' => $base.'/dashboard',
            'support_tickets' => $base.'/support-tickets',
            'onboarding_ticket' => $ticketSecureId
                ? $base.'/support-tickets/'.$ticketSecureId
                : null,
            'crm_login_links' => $base.'/crm-login-links',
        ];
    }
}
