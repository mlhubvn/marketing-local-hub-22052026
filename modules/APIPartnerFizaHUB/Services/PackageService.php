<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Support\PartnerApiException;

class PackageService
{
    /** @var list<string> */
    private const LIMIT_WHITELIST = [
        'max_businesses',
        'max_campaigns',
        'max_landing_pages',
        'max_qr_codes',
        'max_team_members',
    ];

    public function __construct(
        protected SupportTicketBridge $integrations,
        protected PackageAssignmentService $packages,
    ) {}

    /**
     * Public package catalog (partner-facing).
     *
     * @return array<string, mixed>
     */
    public function list(): array
    {
        return $this->packages->catalog();
    }

    /**
     * @return array<string, mixed>
     */
    public function forBusiness(string $externalBusinessId): array
    {
        $integration = $this->integrations->findIntegrationOrFail($externalBusinessId);
        $user = User::query()->with('plan')->find($integration->mlhub_user_id);

        if (! $user) {
            // Mirrors the same defensive check in OnboardingService::updateExistingMapping:
            // the mapping row survived while the mapped user was deleted underneath it.
            throw PartnerApiException::make(
                'integration_broken',
                __('Liên kết với MKT cho business này bị lỗi. Vui lòng liên hệ MKT để được hỗ trợ khôi phục.'),
                409,
                ['next_action' => 'contact_support']
            );
        }

        $plan = $user->plan;
        $onboarding = PartnerOnboardingRequest::query()
            ->where('partner_code', $integration->partner_code)
            ->where('external_business_id', $externalBusinessId)
            ->latest('id')
            ->first();
        $effectivePackage = $integration->package_code
            ?: (string) config('modules.apipartnerfizahub.default_package', 'free');

        return [
            'effective_package' => $effectivePackage,
            'requested_package' => $onboarding?->requested_package_code ?: $effectivePackage,
            'approved_package' => $onboarding?->approved_package_code,
            'package_name' => $plan?->name,
            'plan_slug' => $plan?->slug,
            'status' => $this->planStatus($user, $plan),
            'starts_at' => optional($user->plan_started_at)?->utc()?->toIso8601String(),
            'expires_at' => optional($user->plan_expires_at)?->utc()?->toIso8601String(),
            'is_trial' => $this->isTrial($user, $plan),
            'mapping_status' => $integration->status,
            'limits' => $this->whitelistedLimits($plan),
        ];
    }

    private function planStatus(User $user, ?AdminPlan $plan): string
    {
        if (! $plan) {
            return 'none';
        }

        if ($user->plan_expires_at && $user->plan_expires_at->isPast()) {
            return 'expired';
        }

        return $plan->status ? 'active' : 'inactive';
    }

    private function isTrial(User $user, ?AdminPlan $plan): bool
    {
        if (! $plan || (int) ($plan->trial_day ?? 0) <= 0) {
            return false;
        }

        if (! $user->plan_started_at) {
            return false;
        }

        return $user->plan_started_at->copy()->addDays((int) $plan->trial_day)->isFuture();
    }

    /**
     * @return array<string, mixed>
     */
    private function whitelistedLimits(?AdminPlan $plan): array
    {
        $permissions = is_array($plan?->permissions) ? $plan->permissions : [];
        $limits = [];

        foreach (self::LIMIT_WHITELIST as $key) {
            if (array_key_exists($key, $permissions)) {
                $limits[$key] = $permissions[$key];
            }
        }

        return $limits;
    }
}
