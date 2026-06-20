<?php

namespace Modules\CustomMLHUB\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Modules\CustomMLHUB\Console\Commands\MLHUBInstallCommand;
use Modules\CustomMLHUB\Console\Commands\MLHUBSyncEnvOptionsCommand;
use Modules\CustomMLHUB\Console\Commands\MLHUBUpdateCommand;
use Modules\CustomMLHUB\Support\MLHUBAIAssistant\MLHUBAIAssistantService;
use Modules\CustomMLHUB\Support\MLHUBAIAssistant\MLHUBAIContextBuilder;
use Modules\CustomMLHUB\Support\MLHUBAIAssistant\MLHUBAIIntentResolver;
use Modules\CustomMLHUB\Support\MLHUBAIAssistant\MLHUBAIResponseComposer;

class CustomMLHUBServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'custommlhub');

        $this->app->singleton(MLHUBAIContextBuilder::class);
        $this->app->singleton(MLHUBAIIntentResolver::class);
        $this->app->singleton(MLHUBAIResponseComposer::class);
        $this->app->singleton(MLHUBAIAssistantService::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'custommlhub');

        if ($this->app->runningInConsole()) {
            $this->commands([
                MLHUBInstallCommand::class,
                MLHUBUpdateCommand::class,
                MLHUBSyncEnvOptionsCommand::class,
            ]);
        }

        register_credit_action([
            'key' => 'mlhub_ai_chat',
            'plan_key' => 'credit_cost_mlhub_ai_chat',
            'label' => __('MLHUB AI Chat'),
            'default_cost' => 1,
            'order' => 21,
            'description' => __('Credits deducted when MLHUB AI answers with the OpenAI/Gemini layer.'),
        ]);

        register_user_sidebar_item('overview', [
            'label' => __('MLHUB AI'),
            'route_name' => 'portal.chatmlhubai',
            'active_when' => ['portal.chatmlhubai'],
            'icon' => 'fa-light fa-robot',
            'order' => 15,
            'visible' => fn (): bool => Route::has('portal.chatmlhubai')
                && (auth()->user()?->canUsePlanFeature('mlhub') ?? false),
        ]);
    }
}
