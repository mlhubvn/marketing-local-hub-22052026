<?php

namespace Modules\AppBusinessLocations\Providers;

use Illuminate\Support\ServiceProvider;

class AppBusinessLocationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appbusinesslocations');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appbusinesslocations');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        // Locations remain available inside each Business workspace.
    }
}
