<?php

namespace Modules\AdminCache\Actions;

use Illuminate\Support\Facades\Artisan;
use Modules\AdminCache\Actions\Contracts\CacheAction;
use Throwable;

class OptimizeApplicationAction implements CacheAction
{
    public function key(): string
    {
        return 'optimize';
    }

    public function title(): string
    {
        return 'Optimize';
    }

    public function description(): string
    {
        return 'Rebuilds safe Laravel caches for production without rebuilding routes.';
    }

    public function whenToUse(): string
    {
        return 'Use after deployment, module changes, or cache cleanup when the site should run with refreshed config, event, and view caches.';
    }

    public function afterRunning(): string
    {
        return 'Config, event, and view caches are refreshed. Route cache is skipped to avoid the live routes-v7.php race.';
    }

    public function icon(): string
    {
        return 'fa-bolt';
    }

    public function buttonLabel(): string
    {
        return 'Optimize safe caches';
    }

    public function buttonVariant(): string
    {
        return 'success';
    }

    public function confirmMessage(): string
    {
        return 'This rebuilds safe optimization caches and skips route cache to keep scheduled tasks stable.';
    }

    public function confirmTitle(): string
    {
        return 'Are you absolutely sure?';
    }

    public function handle(): string
    {
        try {
            Artisan::call('optimize', ['--except' => 'routes']);

            return __('Application optimized successfully. Route cache was skipped to keep scheduled tasks stable.');
        } catch (Throwable) {
            Artisan::call('optimize:clear', ['--except' => 'routes']);

            return __('Optimization cleared instead, because your config files are not serializable.');
        }
    }
}
