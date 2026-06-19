<?php

namespace Modules\AppReviewBooster\Providers;

use Illuminate\Support\ServiceProvider;

class AppReviewBoosterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appreviewbooster');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appreviewbooster');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        register_user_sidebar_item('growth-tools', [
            'label' => 'Review Booster',
            'route_name' => 'portal.review-booster',
            'active_when' => ['portal.review-booster'],
            'icon' => 'fa-light fa-star',
            'order' => 10,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('mlhub') ?? true,
        ]);

    }
}
