<?php

use Illuminate\Database\Migrations\Migration;
use Modules\AdminPlans\Models\AdminPlan;

return new class extends Migration {
    public function up(): void
    {
        if (! class_exists(AdminPlan::class)) {
            return;
        }

        $defaults = [
            'starter' => ['webhook_automation' => false, 'max_webhook_automations' => 0, 'webhooks_per_month' => 0, 'webhook_custom_headers' => false, 'webhook_retry' => false],
            'growth' => ['webhook_automation' => true, 'max_webhook_automations' => 10, 'webhooks_per_month' => 5000, 'webhook_custom_headers' => true, 'webhook_retry' => true],
            'agency' => ['webhook_automation' => true, 'max_webhook_automations' => -1, 'webhooks_per_month' => -1, 'webhook_custom_headers' => true, 'webhook_retry' => true],
        ];

        AdminPlan::query()->get()->each(function (AdminPlan $plan) use ($defaults): void {
            $slug = strtolower((string) ($plan->slug ?? $plan->name ?? ''));
            $bucket = str_contains($slug, 'agency') ? 'agency' : (str_contains($slug, 'growth') ? 'growth' : 'starter');
            $permissions = (array) ($plan->permissions ?? []);
            $plan->forceFill(['permissions' => array_merge($defaults[$bucket], $permissions)])->save();
        });
    }

    public function down(): void
    {
        if (! class_exists(AdminPlan::class)) {
            return;
        }

        AdminPlan::query()->get()->each(function (AdminPlan $plan): void {
            $permissions = (array) ($plan->permissions ?? []);
            foreach (['webhook_automation', 'max_webhook_automations', 'webhooks_per_month', 'webhook_custom_headers', 'webhook_retry'] as $key) {
                unset($permissions[$key]);
            }
            $plan->forceFill(['permissions' => $permissions])->save();
        });
    }
};
