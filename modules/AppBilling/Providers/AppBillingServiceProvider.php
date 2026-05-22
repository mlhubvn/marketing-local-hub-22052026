<?php

namespace Modules\AppBilling\Providers;

use Illuminate\Support\ServiceProvider;

class AppBillingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appbilling');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appbilling');

        register_user_sidebar_section('team-billing', __('Account'), 600);
        register_user_sidebar_item('team-billing', [
            'label' => 'Plans',
            'route_name' => 'portal.packages',
            'active_when' => ['portal.packages'],
            'icon' => 'fa-light fa-box-open',
            'order' => 20,
        ]);

        register_user_sidebar_item('team-billing', [
            'label' => 'Billing',
            'route_name' => 'portal.billing',
            'active_when' => ['portal.billing', 'portal.invoices'],
            'icon' => 'fa-light fa-credit-card',
            'order' => 30,
        ]);
    }
}
