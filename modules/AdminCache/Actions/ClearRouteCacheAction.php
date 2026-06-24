<?php

namespace Modules\AdminCache\Actions;

use Modules\AdminCache\Actions\Contracts\CacheAction;

class ClearRouteCacheAction implements CacheAction
{
    public function key(): string
    {
        return 'route';
    }

    public function title(): string
    {
        return 'Route cache';
    }

    public function description(): string
    {
        return 'Route cache is managed during deployment to protect scheduled tasks.';
    }

    public function icon(): string
    {
        return 'fa-route';
    }

    public function buttonLabel(): string
    {
        return 'Managed';
    }

    public function buttonVariant(): string
    {
        return 'outline';
    }

    public function confirmMessage(): string
    {
        return 'Route cache is cleared safely during container startup. Redeploy to refresh route cache state.';
    }

    public function confirmTitle(): string
    {
        return 'Are you absolutely sure?';
    }

    public function handle(): string
    {
        return __('Route cache is managed during deployment and is not cleared while the application is live.');
    }
}
