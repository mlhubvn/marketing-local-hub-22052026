<?php

namespace Modules\AppLeadForms\Providers;

use Illuminate\Support\ServiceProvider;

class AppLeadFormsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appleadforms');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appleadforms');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        register_user_sidebar_item('growth-tools', [
            'label' => 'Lead Forms',
            'route_name' => 'portal.lead-forms',
            'active_when' => ['portal.lead-forms'],
            'icon' => 'fa-light fa-clipboard-list-check',
            'order' => 50,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('localboost') ?? true,
        ]);
    }
}
