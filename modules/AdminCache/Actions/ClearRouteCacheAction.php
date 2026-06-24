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
        return 'Route cache is managed during deployment. Live clearing or rebuilding is disabled to protect scheduled tasks and queue workers.';
    }

    public function whenToUse(): string
    {
        return 'After adding routes or changing modules, redeploy the app so the container startup refreshes route state safely.';
    }

    public function afterRunning(): string
    {
        return 'No route cache is changed from this page. Redeploy clears stale route cache files before web, queue, and scheduler processes start.';
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
        return 'Route cache is intentionally managed by deployment. Redeploy the app to refresh routes safely.';
    }

    public function confirmTitle(): string
    {
        return 'Are you absolutely sure?';
    }

    public function handle(): string
    {
        return __('Route cache is managed during deployment. Redeploy the app to refresh routes safely.');
    }
}
