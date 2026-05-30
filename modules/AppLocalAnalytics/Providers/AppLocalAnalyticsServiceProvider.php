<?php

namespace Modules\AppLocalAnalytics\Providers;

use Illuminate\Support\ServiceProvider;

class AppLocalAnalyticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.applocalanalytics');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'applocalanalytics');

        register_user_sidebar_item('analytics', [
            'label' => __('All Reports'),
            'route_name' => 'portal.reports',
            'active_when' => ['portal.reports'],
            'icon' => 'fa-light fa-chart-line',
            'order' => 10,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('localboost') ?? true,
        ]);

        register_admin_dashboard_item('localboost.snapshot', [
            'title' => 'LocalBoost AI',
            'view' => 'applocalanalytics::dashboard.localboost-snapshot',
            'width' => 'full',
            'order' => 35,
            'data' => fn () => [
                'metrics' => [
                    'businesses' => \Modules\AppBusinessProfiles\Models\LocalBusiness::query()->count(),
                    'campaigns' => \Modules\AppQRCampaigns\Models\QrCampaign::query()->count(),
                    'landing_pages' => \Modules\AppLandingPages\Models\LandingPage::query()->count(),
                    'visits' => \Modules\AppQRCampaigns\Models\QrScan::query()->count()
                        + (int) \Modules\AppLandingPages\Models\LandingPage::query()->sum('visits_count'),
                    'conversions' => (int) \Modules\AppLandingPages\Models\LandingPage::query()->sum('conversions_count'),
                ],
                'topCampaigns' => \Modules\AppQRCampaigns\Models\QrCampaign::query()
                    ->with('business')
                    ->withCount('scans')
                    ->orderByDesc('scans_count')
                    ->limit(5)
                    ->get(),
            ],
        ]);
    }
}
