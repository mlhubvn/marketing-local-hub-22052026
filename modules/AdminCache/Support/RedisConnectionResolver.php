<?php

namespace Modules\AdminCache\Support;

use Illuminate\Support\Facades\Redis;
use Throwable;

class RedisConnectionResolver
{
    public static function sessionConnectionName(): string
    {
        if (filled(config('session.connection'))) {
            return (string) config('session.connection');
        }

        $storeName = (string) (config('session.store') ?: config('session.driver', 'redis'));
        $store = config("cache.stores.{$storeName}");

        if (! is_array($store)) {
            return 'default';
        }

        return match ($store['driver'] ?? '') {
            'redis' => (string) ($store['connection'] ?? 'cache'),
            default => 'default',
        };
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

    public static function flush(string $connection): void
    {
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
            $items['session_redis_database'] = config("database.redis.{$connection}.database");
            $items['session_redis_ping_error'] = self::ping($connection);
        }

        if ($cacheStore === 'redis') {
            $connection = self::cacheConnectionName();
            $items['cache_redis_connection'] = $connection;
            $items['cache_redis_database'] = config("database.redis.{$connection}.database");
            $items['cache_redis_ping_error'] = self::ping($connection);
        }

        return $items;
    }
}
