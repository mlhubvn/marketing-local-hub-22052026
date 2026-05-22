<?php

namespace Modules\AppAIReview\Providers;

use Illuminate\Support\ServiceProvider;

class AppAIReviewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'modules.appaireview');
    }

    public function boot(): void
    {
        // AI Review has been removed from the product surface.
    }
}
