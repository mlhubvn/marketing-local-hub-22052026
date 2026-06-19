<?php

namespace Modules\AppLandingPages\Providers;

use Illuminate\Support\ServiceProvider;

class AppLandingPagesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.applandingpages');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'applandingpages');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        register_user_sidebar_item('marketing-assets', [
            'label' => 'Landing Pages',
            'route_name' => 'portal.landing-pages',
            'active_when' => ['portal.landing-pages'],
            'icon' => 'fa-light fa-browser',
            'order' => 10,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('mlhub') ?? true,
        ]);
    }
}
