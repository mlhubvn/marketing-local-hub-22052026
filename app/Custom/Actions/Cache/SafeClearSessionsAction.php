<?php

namespace App\Custom\Actions\Cache;

use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Modules\AdminCache\Actions\ClearSessionsAction;
use RuntimeException;

class SafeClearSessionsAction extends ClearSessionsAction
{
    public function handle(): string
    {
        $driver = (string) config('session.driver', 'file');
        $currentSessionId = session()->getId();

        match ($driver) {
            'file' => $this->clearOtherFileSessions($currentSessionId),
            'database' => $this->clearOtherDatabaseSessions($currentSessionId),
            'redis' => $this->clearOtherRedisSessions($currentSessionId),
            default => throw new RuntimeException(__('Session clear is not supported for the current session driver: :driver', ['driver' => $driver])),
        };

        session()->save();

        return __('All sessions cleared successfully. All other users have been logged out.');
    }

    protected function clearOtherFileSessions(string $currentSessionId): void
    {
        $path = storage_path('framework/sessions');

        if (! File::isDirectory($path)) {
            throw new RuntimeException(__('The session storage directory could not be found.'));
        }

        foreach (File::files($path) as $file) {
            if ($file->getFilename() === $currentSessionId) {
                continue;
            }

            File::delete($file->getPathname());
        }
    }

    protected function clearOtherDatabaseSessions(string $currentSessionId): void
    {
        $table = (string) config('session.table', 'sessions');

        if (! Schema::hasTable($table)) {
            throw new RuntimeException(__('The session table [:table] does not exist.', ['table' => $table]));
        }

        DB::table($table)->where('id', '!=', $currentSessionId)->delete();
    }

    protected function clearOtherRedisSessions(string $currentSessionId): void
    {
        $storeName = (string) (config('session.store') ?: 'redis');
        $store = cache()->store($storeName);

        if ($store instanceof RedisStore) {
            $this->clearOtherRedisStoreSessions($store, $currentSessionId);

            return;
        }

        $connectionName = config('session.connection');
        $connection = Redis::connection($connectionName);
        $prefix = (string) config('database.redis.options.prefix', '');

        $this->scanDeleteRedisKeys($connection, $prefix, $currentSessionId);
    }

    protected function clearOtherRedisStoreSessions(RedisStore $store, string $currentSessionId): void
    {
        $this->scanDeleteRedisKeys(
            $store->connection(),
            $store->getPrefix(),
            $currentSessionId,
        );
    }

    protected function scanDeleteRedisKeys(mixed $connection, string $prefix, string $currentSessionId): void
    {
        $currentKey = $prefix.$currentSessionId;
        $pattern = $prefix === '' ? '*' : $prefix.'*';
        $cursor = null;

        do {
            $result = $connection->scan($cursor, ['match' => $pattern, 'count' => 100]);

            if (! is_array($result)) {
                break;
            }

            $cursor = $result[0] ?? 0;
            $keys = $result[1] ?? [];

            foreach ($keys as $key) {
                if ((string) $key === $currentKey) {
                    continue;
                }

                $connection->del($key);
            }
        } while ($cursor !== 0 && $cursor !== '0');
    }
}
