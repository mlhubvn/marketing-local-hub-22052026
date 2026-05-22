<?php

namespace Modules\AppQRCampaigns\Providers;

use Illuminate\Support\ServiceProvider;

class AppQRCampaignsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appqrcampaigns');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appqrcampaigns');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        register_user_sidebar_item('marketing-assets', [
            'label' => 'QR Codes',
            'route_name' => 'portal.qr-campaigns',
            'active_when' => ['portal.qr-campaigns'],
            'icon' => 'fa-light fa-qrcode',
            'order' => 20,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('localboost') ?? true,
        ]);

    }
}
