<?php

namespace Modules\AppFeedbackForms\Providers;

use Illuminate\Support\ServiceProvider;

class AppFeedbackFormsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appfeedbackforms');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'appfeedbackforms');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        register_user_sidebar_item('growth-tools', [
            'label' => 'Feedback Forms',
            'route_name' => 'portal.feedback-forms',
            'active_when' => ['portal.feedback-forms'],
            'icon' => 'fa-light fa-message-lines',
            'order' => 40,
            'visible' => fn (): bool => auth()->user()?->canUsePlanFeature('mlhub') ?? true,
        ]);
    }
}
