<?php

namespace Modules\CustomAdminCache\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AdminCache\Actions\ClearSessionsAction;
use Modules\CustomAdminCache\Actions\SafeClearSessionsAction;

class CustomAdminCacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClearSessionsAction::class, SafeClearSessionsAction::class);
    }
}
