<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;

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
        protected SupportTicketBridge $integrations
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forBusiness(string $externalBusinessId): array
    {
        $integration = $this->integrations->findIntegrationOrFail($externalBusinessId);
        $user = User::query()->with('plan')->findOrFail($integration->mlhub_user_id);
        $plan = $user->plan;

        return [
            'package_code' => $integration->package_code ?: (string) config('modules.apipartnerfizahub.default_package', 'base'),
            'package_name' => $plan?->name,
            'plan_slug' => $plan?->slug,
            'status' => $this->planStatus($user, $plan),
            'starts_at' => optional($user->plan_started_at)?->utc()?->toIso8601String(),
            'expires_at' => optional($user->plan_expires_at)?->utc()?->toIso8601String(),
            'is_trial' => $this->isTrial($user, $plan),
            'integration_status' => $integration->status,
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
