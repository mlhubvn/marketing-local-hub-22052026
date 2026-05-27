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

            $permissions['advanced_crm'] ??= $isAgency;
            $permissions['customer_tags'] ??= $isAgency ? -1 : ($isGrowth ? 25 : 0);
            $permissions['customer_segments'] ??= $isAgency ? -1 : ($isGrowth ? 10 : 0);
            $permissions['customer_tasks'] ??= $isAgency ? -1 : ($isGrowth ? 200 : 0);
            $permissions['crm_automations'] ??= $isAgency ? 20 : 0;
            $permissions['crm_activity_retention_days'] ??= 365;

            $plan->forceFill(['permissions' => $permissions])->save();
        });
    }

    public function down(): void
    {
        AdminPlan::query()->each(function (AdminPlan $plan): void {
            $permissions = (array) ($plan->permissions ?? []);
            foreach (['advanced_crm', 'customer_tags', 'customer_segments', 'customer_tasks', 'crm_automations', 'crm_activity_retention_days'] as $key) {
                unset($permissions[$key]);
            }
            $plan->forceFill(['permissions' => $permissions])->save();
        });
    }
};
