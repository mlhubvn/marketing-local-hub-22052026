<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AdminUser\Models\User;
use Modules\AppEmailAutomation\Support\EmailAutomationService;
use Modules\AppMarketingTemplates\Support\TemplatePackService;

/**
 * Dữ liệu phụ trợ giống mysql-cu (sau khi dùng app lần đầu): email hệ thống, template packs, thẻ CRM.
 * Không seed files (logo upload thủ công).
 */
class MLHUBDemoExtrasSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->where('email', (string) config('mlhub.contact_email', 'demo@mlhub.vn'))->first()
            ?? User::query()->orderBy('id')->first();

        if (! $user) {
            return;
        }

        if (class_exists(EmailAutomationService::class)) {
            app(EmailAutomationService::class)->ensureSystemTemplates();
        }

        if (class_exists(TemplatePackService::class)) {
            app(TemplatePackService::class)->ensureSystemPacks();
        }
    }
}
