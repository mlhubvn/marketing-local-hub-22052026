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
            'starter' => ['email_automation' => false, 'max_email_automations' => 0, 'max_email_templates' => 0, 'emails_per_month' => 0, 'automation_delay' => false, 'automation_conditions' => false],
            'growth' => ['email_automation' => true, 'max_email_automations' => 10, 'max_email_templates' => 20, 'emails_per_month' => 5000, 'automation_delay' => true, 'automation_conditions' => true],
            'agency' => ['email_automation' => true, 'max_email_automations' => -1, 'max_email_templates' => -1, 'emails_per_month' => -1, 'automation_delay' => true, 'automation_conditions' => true],
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
            foreach (['email_automation', 'max_email_automations', 'max_email_templates', 'emails_per_month', 'automation_delay', 'automation_conditions'] as $key) {
                unset($permissions[$key]);
            }
            $plan->forceFill(['permissions' => $permissions])->save();
        });
    }
};
