<?php

namespace Modules\CustomAdminCache\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Modules\AdminCache\Actions\ClearSessionsAction;
use Modules\AdminCache\Livewire\CacheIndex;
use Modules\CustomAdminCache\Actions\SafeClearSessionsAction;
use Modules\CustomAdminCache\Livewire\SafeCacheIndex;

class CustomAdminCacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClearSessionsAction::class, SafeClearSessionsAction::class);
    }

    public function boot(): void
    {
        // AdminCache registers the same URI first; override the Livewire class instead of duplicating routes.
        Livewire::component(
            'modules.admin-cache.livewire.cache-index',
            SafeCacheIndex::class,
        );

        Livewire::component(
            CacheIndex::class,
            SafeCacheIndex::class,
        );
    }
}
