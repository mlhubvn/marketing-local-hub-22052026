<?php

namespace Modules\CustomMLHUB\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\AdminUser\Models\User;
use Modules\AppEmailAutomation\Support\EmailAutomationService;
use Modules\AppMarketingTemplates\Support\TemplatePackService;

class MLHUBSystemExtrasSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) config('custommlhub.first_user.email', ''));
        $user = ($email !== '' ? User::query()->where('email', $email)->first() : null)
            ?? User::query()->where('email', (string) config('mlhub.contact_email', ''))->first()
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
