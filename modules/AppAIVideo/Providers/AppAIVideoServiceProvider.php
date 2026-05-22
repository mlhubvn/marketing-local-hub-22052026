<?php

namespace Modules\AppAIVideo\Providers;

use Illuminate\Support\ServiceProvider;

class AppAIVideoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appaivideo');
    }

    public function boot(): void
    {
        // AI Video has been removed from the product surface.
    }
}
