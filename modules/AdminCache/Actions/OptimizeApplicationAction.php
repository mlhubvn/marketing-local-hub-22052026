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
        return 'Run optimize to rebuild safe caches without touching live route cache.';
    }

    public function icon(): string
    {
        return 'fa-bolt';
    }

    public function buttonLabel(): string
    {
        return 'Run optimize';
    }

    public function buttonVariant(): string
    {
        return 'success';
    }

    public function confirmMessage(): string
    {
        return 'This action will rebuild safe optimization caches and skip route cache to keep scheduled tasks stable.';
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
