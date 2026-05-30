<?php

namespace Modules\AdminLog\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\AdminLog\Support\LogManager;

class AdminLogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.adminlog');
        $this->app->singleton(LogManager::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'adminlog');

        register_setting_item('general', [
            'label' => 'Logs',
            'description' => 'View, download, and clear application log files.',
            'route_name' => 'admin-log.index',
            'active_when' => ['admin-log.*'],
            'order' => 130,
        ]);
    }
}
