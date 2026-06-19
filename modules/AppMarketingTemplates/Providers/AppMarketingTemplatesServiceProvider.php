<?php

namespace Modules\AppMarketingTemplates\Providers;

use Illuminate\Support\ServiceProvider;

class AppMarketingTemplatesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appmarketingtemplates');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appmarketingtemplates');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        register_user_sidebar_item('marketing-assets', [
            'label' => 'Templates',
            'route_name' => 'portal.marketing-templates',
            'active_when' => ['portal.marketing-templates'],
            'icon' => 'fa-light fa-grid-2',
            'order' => 30,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('mlhub') ?? true,
        ]);
    }
}
