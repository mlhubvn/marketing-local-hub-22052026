<?php

namespace Modules\CustomDatabaseSequence\Providers;

use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Modules\CustomDatabaseSequence\Console\Commands\SetIdSequenceCommand;
use Modules\CustomDatabaseSequence\Listeners\AdjustAutoIncrementAfterMigrations;

class CustomDatabaseSequenceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/config.php',
            'modules.customdatabasesequence'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SetIdSequenceCommand::class,
            ]);
        }

        Event::listen(
            MigrationsEnded::class,
            AdjustAutoIncrementAfterMigrations::class
        );
    }
}
