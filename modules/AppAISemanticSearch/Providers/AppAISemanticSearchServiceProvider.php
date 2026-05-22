<?php

namespace Modules\AppAISemanticSearch\Providers;

use Illuminate\Support\ServiceProvider;

class AppAISemanticSearchServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appaisemanticsearch');
    }

    public function boot(): void
    {
        // Semantic Search has been removed from the product surface.
    }
}
