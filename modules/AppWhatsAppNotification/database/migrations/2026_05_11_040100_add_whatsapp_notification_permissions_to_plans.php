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
            'starter' => ['whatsapp_notification' => false, 'max_whatsapp_notifications' => 0, 'max_whatsapp_templates' => 0, 'whatsapp_messages_per_month' => 0, 'whatsapp_cloud_api' => false, 'whatsapp_template_messages' => false],
            'growth' => ['whatsapp_notification' => true, 'max_whatsapp_notifications' => 10, 'max_whatsapp_templates' => 20, 'whatsapp_messages_per_month' => 1000, 'whatsapp_cloud_api' => true, 'whatsapp_template_messages' => true],
            'agency' => ['whatsapp_notification' => true, 'max_whatsapp_notifications' => -1, 'max_whatsapp_templates' => -1, 'whatsapp_messages_per_month' => -1, 'whatsapp_cloud_api' => true, 'whatsapp_template_messages' => true],
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
            foreach (['whatsapp_notification', 'max_whatsapp_notifications', 'max_whatsapp_templates', 'whatsapp_messages_per_month', 'whatsapp_cloud_api', 'whatsapp_template_messages'] as $key) {
                unset($permissions[$key]);
            }
            $plan->forceFill(['permissions' => $permissions])->save();
        });
    }
};
