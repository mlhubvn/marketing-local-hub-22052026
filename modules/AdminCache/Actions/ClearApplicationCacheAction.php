<?php

namespace Modules\AdminCache\Actions;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Modules\AdminCache\Actions\Contracts\CacheAction;
use Modules\AdminCache\Support\RedisConnectionResolver;
use RuntimeException;

class ClearApplicationCacheAction implements CacheAction
{
    public function key(): string
    {
        return 'app';
    }

    public function title(): string
    {
        return 'Application cache';
    }

    public function description(): string
    {
        return 'Clears the Laravel application cache store. Redis cache is flushed only when it is isolated from queue and session data.';
    }

    public function whenToUse(): string
    {
        return 'Use when settings, dashboard data, theme options, plan limits, or cached integrations still show old values after an update.';
    }

    public function afterRunning(): string
    {
        return 'The next request rebuilds cache entries. The first load may be slightly slower; users stay logged in when Redis databases are isolated.';
    }

    public function icon(): string
    {
        return 'fa-database';
    }

    public function buttonLabel(): string
    {
        return 'Clear app cache';
    }

    public function buttonVariant(): string
    {
        return 'outline';
    }

    public function confirmMessage(): string
    {
        return 'This removes cached application data from the configured cache store. It does not clear user sessions when Redis isolation is configured correctly.';
    }

    public function confirmTitle(): string
    {
        return 'Are you absolutely sure?';
    }

    public function handle(): string
    {
        $cacheStore = (string) config('cache.default', 'file');
        $redisConnection = null;

        if ($cacheStore === 'redis') {
            $redisConnection = RedisConnectionResolver::cacheConnectionName();
            $pingError = RedisConnectionResolver::ping($redisConnection);

            if ($pingError !== null) {
                throw new RuntimeException(__('Could not reach Redis for application cache (:connection). Check REDIS_HOST, REDIS_USERNAME, and REDIS_PASSWORD on Coolify. Error: :message', [
                    'connection' => $redisConnection,
                    'message' => $pingError,
                ]));
            }

            RedisConnectionResolver::flush($redisConnection);
        }

        Artisan::call('cache:clear');

        Log::info('admin_cache.application_cache_cleared', [
            'cache_store' => $cacheStore,
            'redis_connection' => $redisConnection,
        ]);

        return __('Application cache cleared. Laravel will rebuild cached data on the next request.');
    }
}
