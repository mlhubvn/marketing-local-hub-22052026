<?php

namespace Modules\APIPartnerFizaHUB\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Services\DashboardService;
use Throwable;

class WarmPartnerDashboardsCommand extends Command
{
    protected $signature = 'fizahub:dashboard-warm {--limit=200 : Maximum mapped integrations to warm}';

    protected $description = 'Warm the FizaHUB partner dashboard cache for active integrations.';

    public function handle(DashboardService $dashboard): int
    {
        if (! Schema::hasTable('partner_integrations')) {
            $this->warn('partner_integrations table is not available; skipping.');

            return self::SUCCESS;
        }

        $timezone = (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
        $to = CarbonImmutable::now($timezone)->startOfDay();
        $from = $to->subDays(29);
        $warmed = 0;

        PartnerIntegration::query()
            ->where('partner_code', (string) config('modules.apipartnerfizahub.partner_code', 'fizahub'))
            ->where('status', 'active')
            ->whereNotNull('mlhub_user_id')
            ->whereNotNull('mlhub_business_id')
            ->orderBy('id')
            ->limit((int) $this->option('limit'))
            ->each(function (PartnerIntegration $integration) use ($dashboard, $from, $to, &$warmed): void {
                try {
                    $dashboard->summarizeCached($integration, $from, $to, true);
                    $warmed++;
                } catch (Throwable $exception) {
                    $this->warn("Failed to warm {$integration->external_business_id}: {$exception->getMessage()}");
                }
            });

        $this->info("Warmed {$warmed} FizaHUB dashboards.");

        return self::SUCCESS;
    }
}
