<?php

use Illuminate\Database\Migrations\Migration;
use Modules\AdminPlans\Models\AdminPlan;

return new class extends Migration {
    public function up(): void
    {
        AdminPlan::query()->each(function (AdminPlan $plan): void {
            $permissions = (array) ($plan->permissions ?? []);
            $slug = strtolower((string) ($plan->slug ?? $plan->name ?? ''));
            $isAgency = str_contains($slug, 'agency') || str_contains($slug, 'pro');
            $isGrowth = str_contains($slug, 'growth');

            $permissions['loyalty_stamp_cards'] ??= $isAgency;
            $permissions['max_loyalty_cards'] ??= $isAgency ? -1 : ($isGrowth ? 5 : 0);
            $permissions['max_loyalty_customers'] ??= $isAgency ? -1 : ($isGrowth ? 500 : 0);
            $permissions['max_referral_campaigns'] ??= $isAgency ? -1 : ($isGrowth ? 3 : 0);
            $permissions['loyalty_rewards'] ??= $isAgency;
            $permissions['loyalty_staff_redeem'] ??= $isAgency;

            $plan->forceFill(['permissions' => $permissions])->save();
        });
    }

    public function down(): void
    {
        AdminPlan::query()->each(function (AdminPlan $plan): void {
            $permissions = (array) ($plan->permissions ?? []);

            foreach (['loyalty_stamp_cards', 'max_loyalty_cards', 'max_loyalty_customers', 'max_referral_campaigns', 'loyalty_rewards', 'loyalty_staff_redeem'] as $key) {
                unset($permissions[$key]);
            }

            $plan->forceFill(['permissions' => $permissions])->save();
        });
    }
};
