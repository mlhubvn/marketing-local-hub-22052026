<?php

namespace Modules\CustomInstaller\Providers;

use App\Installer\Support\InstallerService;
use Illuminate\Support\ServiceProvider;
use Modules\CustomInstaller\Support\SafeInstallerService;

class CustomInstallerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Swap the core InstallerService with our subclass that auto-wipes
        // the database instead of throwing "not empty" during step=setup.
        // Toggle via env: set CUSTOM_INSTALLER_AUTO_WIPE=false to fall back
        // to the original safety check.
        if (env('CUSTOM_INSTALLER_AUTO_WIPE', true)) {
            $this->app->bind(InstallerService::class, SafeInstallerService::class);
        }
    }
}
