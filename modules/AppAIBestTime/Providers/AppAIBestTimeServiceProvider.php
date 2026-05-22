<?php

namespace Modules\AppAIBestTime\Providers;

use Illuminate\Support\ServiceProvider;

class AppAIBestTimeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appaibesttime');
    }

    public function boot(): void
    {
        // Best Time has been removed from the product surface.
    }
}
