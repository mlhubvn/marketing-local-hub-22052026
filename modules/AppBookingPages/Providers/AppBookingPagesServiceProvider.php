<?php

namespace Modules\AppBookingPages\Providers;

use Illuminate\Support\ServiceProvider;

class AppBookingPagesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appbookingpages');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appbookingpages');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        register_user_sidebar_item('growth-tools', [
            'label' => 'Booking Pages',
            'route_name' => 'portal.booking-pages',
            'active_when' => ['portal.booking-pages'],
            'icon' => 'fa-light fa-calendar-check',
            'order' => 20,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('localboost') ?? true,
        ]);
    }
}
