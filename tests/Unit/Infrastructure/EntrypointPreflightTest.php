<?php

use Symfony\Component\Process\Process;

function runEntrypointPreflight(array $environment): Process
{
    $process = new Process(
        ['sh', base_path('docker/entrypoint.sh'), 'true'],
        base_path(),
        array_merge([
            'APP_KEY' => 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=',
            'APP_INSTALLED' => 'false',
            'CACHE_STORE' => 'array',
            'QUEUE_CONNECTION' => 'sync',
            'SESSION_DRIVER' => 'array',
        ], $environment),
    );
    $process->setTimeout(20);
    $process->run();

    return $process;
}

test('entrypoint rejects a redis URL whose query-bearing database conflicts with cache', function (): void {
    $process = runEntrypointPreflight([
        'CACHE_STORE' => 'redis',
        'QUEUE_CONNECTION' => 'redis',
        'REDIS_CACHE_DB' => '1',
        'REDIS_URL' => 'redis://redis:6379/1?persistent=1',
    ]);

    expect($process->isSuccessful())->toBeFalse()
        ->and($process->getErrorOutput())->toContain('REDIS_URL must not contain query parameters');
});

test('entrypoint rejects redis database query overrides', function (): void {
    $process = runEntrypointPreflight([
        'QUEUE_CONNECTION' => 'redis',
        'REDIS_URL' => 'redis://redis:6379/0?database=1',
    ]);

    expect($process->isSuccessful())->toBeFalse()
        ->and($process->getErrorOutput())->toContain('REDIS_URL must not contain query parameters');
});

test('entrypoint rejects encoded redis database paths', function (): void {
    $process = runEntrypointPreflight([
        'QUEUE_CONNECTION' => 'redis',
        'REDIS_URL' => 'redis://redis:6379/%31',
    ]);

    expect($process->isSuccessful())->toBeFalse()
        ->and($process->getErrorOutput())->toContain('REDIS_URL database path must be an unencoded integer');
});

test('entrypoint rejects redis database paths with a trailing slash', function (): void {
    $process = runEntrypointPreflight([
        'QUEUE_CONNECTION' => 'redis',
        'REDIS_URL' => 'redis://redis:6379/1/',
    ]);

    expect($process->isSuccessful())->toBeFalse()
        ->and($process->getErrorOutput())->toContain('REDIS_URL database path must be exactly /0');
});

test('entrypoint rejects redis database paths with extra segments', function (): void {
    $process = runEntrypointPreflight([
        'QUEUE_CONNECTION' => 'redis',
        'REDIS_URL' => 'redis://redis:6379/1/anything',
    ]);

    expect($process->isSuccessful())->toBeFalse()
        ->and($process->getErrorOutput())->toContain('REDIS_URL database path must be exactly /0');
});

test('entrypoint rejects cache locks outside the cache connection', function (): void {
    $process = runEntrypointPreflight([
        'CACHE_STORE' => 'redis',
        'REDIS_CACHE_LOCK_CONNECTION' => 'default',
    ]);

    expect($process->isSuccessful())->toBeFalse()
        ->and($process->getErrorOutput())->toContain('REDIS_CACHE_LOCK_CONNECTION must be cache');
});

test('entrypoint rejects a zero queue worker timeout', function (): void {
    $process = runEntrypointPreflight([
        'QUEUE_CONNECTION' => 'redis',
        'QUEUE_WORKER_TIMEOUT' => '0',
    ]);

    expect($process->isSuccessful())->toBeFalse()
        ->and($process->getErrorOutput())->toContain('QUEUE_WORKER_TIMEOUT must be a positive integer');
});
