<?php

namespace Modules\CustomMLHUB\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\CustomMLHUB\Console\Commands\MLHUBInstallCommand;

class CustomMLHUBServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'custommlhub');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MLHUBInstallCommand::class,
            ]);
        }
    }
}
