<?php

namespace Modules\AppCouponCampaigns\Providers;

use Illuminate\Support\ServiceProvider;

class AppCouponCampaignsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appcouponcampaigns');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appcouponcampaigns');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        register_user_sidebar_item('growth-tools', [
            'label' => 'Coupons',
            'route_name' => 'portal.coupon-campaigns',
            'active_when' => ['portal.coupon-campaigns'],
            'icon' => 'fa-light fa-ticket',
            'order' => 30,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('mlhub') ?? true,
        ]);

    }
}
