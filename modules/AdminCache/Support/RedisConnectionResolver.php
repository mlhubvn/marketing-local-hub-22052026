<?php

namespace Modules\AdminCache\Support;

use Illuminate\Support\ConfigurationUrlParser;
use Illuminate\Support\Facades\Redis;
use RuntimeException;
use Throwable;

class RedisConnectionResolver
{
    public static function sessionConnectionName(): string
    {
        if (filled(config('session.connection'))) {
            return (string) config('session.connection');
        }

        return 'default';
    }

    public static function cacheConnectionName(): string
    {
        $storeName = (string) config('cache.default', 'file');
        $store = config("cache.stores.{$storeName}");

        if (! is_array($store)) {
            return 'default';
        }

        return match ($store['driver'] ?? '') {
            'redis' => (string) ($store['connection'] ?? 'cache'),
            default => 'default',
        };
    }

    public static function cacheLockConnectionName(): ?string
    {
        $storeName = (string) config('cache.default', 'file');
        $store = config("cache.stores.{$storeName}");

        if (! is_array($store) || ($store['driver'] ?? '') !== 'redis') {
            return null;
        }

        return (string) ($store['lock_connection'] ?? $store['connection'] ?? 'cache');
    }

    public static function queueConnectionName(): ?string
    {
        $queueName = (string) config('queue.default', 'sync');
        $queue = config("queue.connections.{$queueName}");

        if (! is_array($queue) || ($queue['driver'] ?? '') !== 'redis') {
            return null;
        }

        return (string) ($queue['connection'] ?? 'default');
    }

    public static function flush(string $connection): void
    {
        self::assertFlushIsIsolated($connection);

        Redis::connection($connection)->flushdb();
    }

    public static function ping(string $connection): ?string
    {
        try {
            $response = Redis::connection($connection)->ping();

            if (is_string($response) && strtoupper($response) !== 'PONG' && $response !== '1' && $response !== 1) {
                return __('Unexpected Redis ping response: :response', ['response' => (string) $response]);
            }

            return null;
        } catch (Throwable $exception) {
            return $exception->getMessage();
        }
    }

    /** @return array<string, mixed> */
    public static function diagnostics(): array
    {
        $sessionDriver = (string) config('session.driver', 'file');
        $cacheStore = (string) config('cache.default', 'file');

        $items = [
            'session_driver' => $sessionDriver,
            'cache_store' => $cacheStore,
        ];

        if ($sessionDriver === 'redis') {
            $connection = self::sessionConnectionName();
            $items['session_redis_connection'] = $connection;
            $items['session_redis_database'] = self::effectiveDatabase($connection);
            $items['session_redis_ping_error'] = self::ping($connection);
        }

        if ($cacheStore === 'redis') {
            $connection = self::cacheConnectionName();
            $items['cache_redis_connection'] = $connection;
            $items['cache_redis_database'] = self::effectiveDatabase($connection);
            $items['cache_redis_ping_error'] = self::ping($connection);

            $lockConnection = self::cacheLockConnectionName();
            $items['cache_lock_redis_connection'] = $lockConnection;
            $items['cache_lock_redis_database'] = self::effectiveDatabase((string) $lockConnection);
        }

        if (($connection = self::queueConnectionName()) !== null) {
            $items['queue_redis_connection'] = $connection;
            $items['queue_redis_database'] = self::effectiveDatabase($connection);
        }

        $conflicts = self::isolationConflicts();
        $items['redis_databases_isolated'] = $conflicts === [];
        $items['redis_database_conflicts'] = $conflicts;

        return $items;
    }

    public static function assertFlushIsIsolated(string $connection): void
    {
        $targetIdentity = self::connectionIdentity($connection);

        foreach (self::isolationConflicts() as $identity => $purposes) {
            if ($identity !== $targetIdentity) {
                continue;
            }

            throw new RuntimeException(__('Refusing to flush Redis connection :connection because it shares Redis database :database across: :purposes.', [
                'connection' => $connection,
                'database' => self::effectiveDatabase($connection),
                'purposes' => implode(', ', $purposes),
            ]));
        }
    }

    /** @return array<string, list<string>> */
    protected static function isolationConflicts(): array
    {
        $connections = [];

        if ((string) config('session.driver', 'file') === 'redis') {
            $connections['session'] = self::sessionConnectionName();
        }

        if ((string) config('cache.default', 'file') === 'redis') {
            $connections['cache'] = self::cacheConnectionName();
            $connections['cache_lock'] = (string) self::cacheLockConnectionName();
        }

        if (($queueConnection = self::queueConnectionName()) !== null) {
            $connections['queue'] = $queueConnection;
        }

        $grouped = [];

        foreach ($connections as $purpose => $connection) {
            $grouped[self::connectionIdentity($connection)][] = $purpose;
        }

        return array_filter(
            $grouped,
            fn (array $purposes): bool => count(array_unique(array_map(
                self::isolationPurpose(...),
                $purposes,
            ))) > 1,
        );
    }

    protected static function isolationPurpose(string $purpose): string
    {
        return $purpose === 'cache_lock' ? 'cache' : $purpose;
    }

    protected static function connectionIdentity(string $connection): string
    {
        $config = self::effectiveConfiguration($connection);
        $host = strtolower((string) ($config['host'] ?? '127.0.0.1'));
        $port = (int) ($config['port'] ?? 6379);

        return "{$host}:{$port}/".self::effectiveDatabase($connection);
    }

    protected static function effectiveDatabase(string $connection): int
    {
        return (int) (self::effectiveConfiguration($connection)['database'] ?? 0);
    }

    /** @return array<string, mixed> */
    protected static function effectiveConfiguration(string $connection): array
    {
        return (new ConfigurationUrlParser)->parseConfiguration(
            (array) config("database.redis.{$connection}", []),
        );
    }
}
