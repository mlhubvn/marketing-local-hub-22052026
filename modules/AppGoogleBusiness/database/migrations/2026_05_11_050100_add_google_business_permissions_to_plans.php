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
            $isGrowth = str_contains($slug, 'growth') || str_contains($slug, 'yearly');

            $permissions['google_business'] ??= $isAgency || $isGrowth;
            $permissions['max_google_business_connections'] ??= $isAgency ? -1 : ($isGrowth ? 3 : 0);
            $permissions['max_google_business_locations'] ??= $isAgency ? -1 : ($isGrowth ? 10 : 0);
            $permissions['google_review_sync'] ??= $isAgency || $isGrowth;
            $permissions['google_review_reply'] ??= $isAgency;
            $permissions['google_business_insights'] ??= $isAgency;
            $permissions['google_business_posts'] ??= $isAgency;

            $plan->forceFill(['permissions' => $permissions])->save();
        });
    }

    public function down(): void
    {
        AdminPlan::query()->each(function (AdminPlan $plan): void {
            $permissions = (array) ($plan->permissions ?? []);

            foreach ([
                'google_business',
                'max_google_business_connections',
                'max_google_business_locations',
                'google_review_sync',
                'google_review_reply',
                'google_business_insights',
                'google_business_posts',
            ] as $key) {
                unset($permissions[$key]);
            }

            $plan->forceFill(['permissions' => $permissions])->save();
        });
    }
};
