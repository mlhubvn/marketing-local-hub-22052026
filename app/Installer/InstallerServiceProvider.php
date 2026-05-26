<?php

namespace App\Installer;

use App\Installer\Support\InstallerState;
use Illuminate\Support\ServiceProvider;

class InstallerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/installer.php', 'installer');
    }

    public function boot(): void
    {
        if (! $this->app->make(InstallerState::class)->isInstalled()) {
            $this->app->setLocale((string) config('installer.default_locale', 'vi'));
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                \Database\Support\MLHUBSetIdSequenceCommand::class,
            ]);
        }

        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'installer');
    }
}
