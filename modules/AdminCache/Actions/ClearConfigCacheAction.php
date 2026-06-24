<?php

namespace Modules\AdminCache\Actions;

use Illuminate\Support\Facades\Artisan;
use Modules\AdminCache\Actions\Contracts\CacheAction;

class ClearConfigCacheAction implements CacheAction
{
    public function key(): string
    {
        return 'config';
    }

    public function title(): string
    {
        return 'Config cache';
    }

    public function description(): string
    {
        return 'Clears Laravel cached configuration so the app reads the current environment and config files again.';
    }

    public function whenToUse(): string
    {
        return 'Use after changing Coolify environment variables or config files when the app still appears to use old values.';
    }

    public function afterRunning(): string
    {
        return 'The next request reloads configuration. It does not log users out; redeploy is still needed when Coolify env values changed.';
    }

    public function icon(): string
    {
        return 'fa-gear-complex-code';
    }

    public function buttonLabel(): string
    {
        return 'Clear config';
    }

    public function buttonVariant(): string
    {
        return 'outline';
    }

    public function confirmMessage(): string
    {
        return 'This clears the cached configuration snapshot. Current requests continue, and new requests reload configuration from the current runtime environment.';
    }

    public function confirmTitle(): string
    {
        return 'Are you absolutely sure?';
    }

    public function handle(): string
    {
        Artisan::call('config:clear');

        return __('Config cache cleared. New requests will reload configuration from the current runtime environment.');
    }
}
