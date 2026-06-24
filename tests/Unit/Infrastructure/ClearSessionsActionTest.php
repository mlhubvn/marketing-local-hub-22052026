<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use Modules\AdminCache\Actions\ClearSessionsAction;

test('clear redis sessions flushes the session connection and logs out the current session', function (): void {
    config([
        'session.driver' => 'redis',
        'session.connection' => 'session',
        'cache.default' => 'redis',
        'cache.stores.redis.connection' => 'cache',
        'queue.default' => 'redis',
        'queue.connections.redis.connection' => 'default',
        'database.redis.default' => ['host' => 'redis', 'port' => 6379, 'database' => 0],
        'database.redis.cache' => ['host' => 'redis', 'port' => 6379, 'database' => 1],
        'database.redis.session' => ['host' => 'redis', 'port' => 6379, 'database' => 2],
    ]);

    Redis::shouldReceive('connection')->twice()->with('session')->andReturnSelf();
    Redis::shouldReceive('ping')->once()->andReturn('PONG');
    Redis::shouldReceive('flushdb')->once()->andReturn(true);

    $guard = Mockery::mock();
    $guard->shouldReceive('logout')->once();
    Auth::shouldReceive('guard')->once()->with('web')->andReturn($guard);

    Session::shouldReceive('invalidate')->once()->andReturn(true);
    Session::shouldReceive('regenerateToken')->once();

    expect(app(ClearSessionsAction::class)->handle())
        ->toBe(__('All sessions cleared. Everyone must log in again, including the current admin session.'));
});
