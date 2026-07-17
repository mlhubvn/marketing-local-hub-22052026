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
        $integration = $this->findIntegration($externalBusinessId);
        $onboarding = $this->latestOnboarding($integration->external_business_id);

        return [
            'external_business_id' => $integration->external_business_id,
            'external_user_id' => $integration->external_user_id,
            'integration_status' => $integration->status,
            'mlhub_user_id' => $integration->mlhub_user_id,
            'mlhub_workspace_id' => $integration->mlhub_workspace_id,
            'mlhub_business_id' => $integration->mlhub_business_id,
            'onboarding' => $onboarding ? [
                'request_id' => $onboarding->request_id,
                'status' => $onboarding->status,
                'current_step' => $onboarding->current_step,
                'status_label' => $onboarding->statusLabel(),
            ] : null,
            'package' => [
                'package_code' => $integration->package_code,
                'requested_package_code' => $onboarding?->requested_package_code,
                'approved_package_code' => $onboarding?->approved_package_code,
            ],
            'last_synced_at' => optional($onboarding?->last_synced_at)?->utc()?->toIso8601String(),
        ];
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

            if ($businessUpdates !== []) {
                $localBusiness->forceFill($businessUpdates)->save();
            }

            $metadata = (array) ($integration->metadata ?? []);

            if (array_key_exists('email', $owner) && $owner['email'] !== '') {
                $metadata['contact_email'] = $this->mapping->normalizeEmail((string) $owner['email']);
            }

            $integration->forceFill([
                'metadata' => $metadata,
            ])->save();
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
}
