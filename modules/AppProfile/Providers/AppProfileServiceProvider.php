<?php

namespace Modules\AppProfile\Providers;

use Illuminate\Support\ServiceProvider;

class AppProfileServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appprofile');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appprofile');

        register_header_item([
            'view' => 'appprofile::partials.header-user-menu',
            'position' => 'end',
            'order' => 80,
        ], 'user');

        register_header_item([
            'view' => 'appprofile::partials.header-user-menu',
            'position' => 'end',
            'order' => 80,
        ], 'admin');

        register_user_sidebar_section('team-billing', __('Account'), 600);
        register_user_sidebar_item('team-billing', [
            'label' => 'Settings',
            'route_name' => 'portal.profile',
            'active_when' => ['portal.profile'],
            'icon' => 'fa-light fa-sliders',
            'order' => 40,
        ]);
    }
}
