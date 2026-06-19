<?php

namespace Modules\AppCustomers\Providers;

use Illuminate\Support\ServiceProvider;

class AppCustomersServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appcustomers');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appcustomers');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        register_user_sidebar_item('local-businesses', [
            'label' => 'Customers',
            'route_name' => 'portal.customers',
            'active_when' => ['portal.customers'],
            'icon' => 'fa-light fa-address-book',
            'order' => 30,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('mlhub') ?? true,
        ]);
    }
}
