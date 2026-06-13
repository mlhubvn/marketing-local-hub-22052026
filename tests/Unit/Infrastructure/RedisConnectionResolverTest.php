<?php

use Illuminate\Support\Facades\Redis;
use Modules\AdminCache\Support\RedisConnectionResolver;

test('redis flush is refused when cache and sessions share an effective database', function (): void {
    config([
        'cache.default' => 'redis',
        'cache.stores.redis.connection' => 'cache',
        'session.driver' => 'redis',
        'session.connection' => 'session',
        'queue.default' => 'redis',
        'queue.connections.redis.connection' => 'default',
        'database.redis.default' => [
            'host' => 'redis',
            'port' => 6379,
            'database' => 0,
        ],
        'database.redis.cache' => [
            'url' => 'redis://redis:6379/0',
            'host' => 'redis',
            'port' => 6379,
            'database' => 1,
        ],
        'database.redis.session' => [
            'url' => 'redis://redis:6379/0',
            'host' => 'redis',
            'port' => 6379,
            'database' => 2,
        ],
    ]);

    Redis::shouldReceive('connection')->never();

    expect(fn () => RedisConnectionResolver::flush('cache'))
        ->toThrow(RuntimeException::class, 'shares Redis database');
});

test('redis diagnostics report isolated queue cache and session databases', function (): void {
    config([
        'cache.default' => 'redis',
        'cache.stores.redis.connection' => 'cache',
        'cache.stores.redis.lock_connection' => 'cache',
        'session.driver' => 'redis',
        'session.connection' => 'session',
        'queue.default' => 'redis',
        'queue.connections.redis.connection' => 'default',
        'database.redis.default' => ['host' => 'redis', 'port' => 6379, 'database' => 0],
        'database.redis.cache' => ['host' => 'redis', 'port' => 6379, 'database' => 1],
        'database.redis.session' => ['host' => 'redis', 'port' => 6379, 'database' => 2],
    ]);

    Redis::shouldReceive('connection')->times(2)->andReturnSelf();
    Redis::shouldReceive('ping')->times(2)->andReturn('PONG');

    expect(RedisConnectionResolver::diagnostics())
        ->toMatchArray([
            'queue_redis_connection' => 'default',
            'queue_redis_database' => 0,
            'cache_redis_connection' => 'cache',
            'cache_redis_database' => 1,
            'session_redis_connection' => 'session',
            'session_redis_database' => 2,
            'redis_databases_isolated' => true,
        ]);
});

test('redis diagnostics report cache locks that share the queue database', function (): void {
    config([
        'cache.default' => 'redis',
        'cache.stores.redis.connection' => 'cache',
        'cache.stores.redis.lock_connection' => 'default',
        'session.driver' => 'redis',
        'session.connection' => 'session',
        'queue.default' => 'redis',
        'queue.connections.redis.connection' => 'default',
        'database.redis.default' => ['host' => 'redis', 'port' => 6379, 'database' => 0],
        'database.redis.cache' => ['host' => 'redis', 'port' => 6379, 'database' => 1],
        'database.redis.session' => ['host' => 'redis', 'port' => 6379, 'database' => 2],
    ]);

    Redis::shouldReceive('connection')->times(2)->andReturnSelf();
    Redis::shouldReceive('ping')->times(2)->andReturn('PONG');

    expect(RedisConnectionResolver::diagnostics())
        ->toMatchArray([
            'cache_lock_redis_connection' => 'default',
            'cache_lock_redis_database' => 0,
            'redis_databases_isolated' => false,
            'redis_database_conflicts' => [
                'redis:6379/0' => ['cache_lock', 'queue'],
            ],
        ]);
});

test('redis diagnostics honor URL query database overrides', function (): void {
    config([
        'cache.default' => 'redis',
        'cache.stores.redis.connection' => 'cache',
        'cache.stores.redis.lock_connection' => 'cache',
        'session.driver' => 'redis',
        'session.connection' => 'session',
        'queue.default' => 'redis',
        'queue.connections.redis.connection' => 'default',
        'database.redis.default' => ['host' => 'redis', 'port' => 6379, 'database' => 0],
        'database.redis.cache' => [
            'url' => 'redis://redis:6379/1?database=0',
            'host' => 'redis',
            'port' => 6379,
            'database' => 1,
        ],
        'database.redis.session' => ['host' => 'redis', 'port' => 6379, 'database' => 2],
    ]);

    Redis::shouldReceive('connection')->times(2)->andReturnSelf();
    Redis::shouldReceive('ping')->times(2)->andReturn('PONG');

    expect(RedisConnectionResolver::diagnostics())
        ->toMatchArray([
            'cache_redis_database' => 0,
            'redis_databases_isolated' => false,
        ]);
});

test('redis diagnostics honor encoded URL database paths', function (): void {
    config([
        'cache.default' => 'redis',
        'cache.stores.redis.connection' => 'cache',
        'cache.stores.redis.lock_connection' => 'cache',
        'session.driver' => 'redis',
        'session.connection' => 'session',
        'queue.default' => 'redis',
        'queue.connections.redis.connection' => 'default',
        'database.redis.default' => [
            'url' => 'redis://redis:6379/%31',
            'host' => 'redis',
            'port' => 6379,
            'database' => 0,
        ],
        'database.redis.cache' => ['host' => 'redis', 'port' => 6379, 'database' => 1],
        'database.redis.session' => ['host' => 'redis', 'port' => 6379, 'database' => 2],
    ]);

    Redis::shouldReceive('connection')->times(2)->andReturnSelf();
    Redis::shouldReceive('ping')->times(2)->andReturn('PONG');

    expect(RedisConnectionResolver::diagnostics())
        ->toMatchArray([
            'queue_redis_database' => 1,
            'redis_databases_isolated' => false,
        ]);
});

test('the application defaults isolate queue cache and session redis databases', function (): void {
    expect(config('queue.connections.redis.connection'))->toBe('default')
        ->and(config('cache.stores.redis.connection'))->toBe('cache')
        ->and(config('cache.stores.redis.lock_connection'))->toBe('cache')
        ->and((int) config('database.redis.default.database'))->toBe(0)
        ->and((int) config('database.redis.cache.database'))->toBe(1)
        ->and((int) config('database.redis.session.database'))->toBe(2)
        ->and(config('database.redis.cache.url'))->toBeNull()
        ->and(config('database.redis.session.url'))->toBeNull()
        ->and(file_get_contents(base_path('.env.example')))->toContain('SESSION_CONNECTION=session');
});
