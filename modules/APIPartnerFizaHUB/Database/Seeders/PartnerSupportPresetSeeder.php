<?php

namespace Modules\APIPartnerFizaHUB\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\APIPartnerFizaHUB\Services\SupportTicketBridge;

class PartnerSupportPresetSeeder extends Seeder
{
    public function run(): void
    {
        app(SupportTicketBridge::class)->ensureDefaultPresets();
    }
}
