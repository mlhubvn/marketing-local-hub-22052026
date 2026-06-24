<?php

namespace Modules\AdminCache\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Modules\AdminCache\Actions\Contracts\CacheAction;
use Modules\AdminCache\Support\RedisConnectionResolver;
use RuntimeException;

class ClearSessionsAction implements CacheAction
{
    public function key(): string
    {
        return 'session';
    }

    public function title(): string
    {
        return 'Clear all sessions';
    }

    public function description(): string
    {
        return 'This will log out all users from the system. Use with caution!';
    }

    public function icon(): string
    {
        return 'fa-user-slash';
    }

    public function buttonLabel(): string
    {
        return 'Clear sessions';
    }

    public function buttonVariant(): string
    {
        return 'danger';
    }

    public function confirmMessage(): string
    {
        return 'This action cannot be undone. It will log out every active user session from the platform immediately.';
    }

    public function confirmTitle(): string
    {
        return 'Are you absolutely sure?';
    }

    public function handle(): string
    {
        $driver = (string) config('session.driver', 'file');
        $redisConnection = null;

        match ($driver) {
            'file' => $this->clearFileSessions(),
            'database' => $this->clearDatabaseSessions(),
            'redis' => $redisConnection = $this->clearRedisSessions(),
            default => throw new RuntimeException(__('Session clear is not supported for the current session driver: :driver', ['driver' => $driver])),
        };

        $this->logoutCurrentSession();

        Log::info('admin_cache.sessions_cleared', [
            'driver' => $driver,
            'redis_connection' => $redisConnection,
        ]);

        return __('All sessions cleared successfully. All users have been logged out.');
    }

    protected function clearFileSessions(): void
    {
        $path = storage_path('framework/sessions');

        if (! File::isDirectory($path)) {
            throw new RuntimeException(__('The session storage directory could not be found.'));
        }

        File::cleanDirectory($path);
    }

    protected function clearDatabaseSessions(): void
    {
        $table = (string) config('session.table', 'sessions');

        if (! Schema::hasTable($table)) {
            throw new RuntimeException(__('The session table [:table] does not exist.', ['table' => $table]));
        }

        DB::table($table)->delete();
    }

    protected function clearRedisSessions(): string
    {
        $connection = RedisConnectionResolver::sessionConnectionName();
        $pingError = RedisConnectionResolver::ping($connection);

        if ($pingError !== null) {
            throw new RuntimeException(__('Could not reach Redis for sessions (:connection). Check REDIS_HOST, REDIS_USERNAME, and REDIS_PASSWORD on Coolify. Error: :message', [
                'connection' => $connection,
                'message' => $pingError,
            ]));
        }

        RedisConnectionResolver::flush($connection);

        return $connection;
    }

    protected function logoutCurrentSession(): void
    {
        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();
    }
}
