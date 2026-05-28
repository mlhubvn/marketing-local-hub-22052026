<?php

namespace App\Custom\Providers;

use App\Custom\Actions\Cache\SafeClearSessionsAction;
use Illuminate\Support\ServiceProvider;
use Modules\AdminCache\Actions\ClearSessionsAction;

class CustomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ClearSessionsAction::class, SafeClearSessionsAction::class);
    }
}
