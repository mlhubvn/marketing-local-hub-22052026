<?php

namespace Modules\CustomAdminCache\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\AdminCache\Actions\ClearSessionsAction;
use Modules\CustomAdminCache\Actions\SafeClearSessionsAction;
use Modules\CustomAdminCache\Livewire\ReloadAfterCacheActionHook;

class CustomAdminCacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClearSessionsAction::class, SafeClearSessionsAction::class);

        $this->app->booting(function (): void {
            Livewire::componentHook(ReloadAfterCacheActionHook::class);
        });
    }
}
