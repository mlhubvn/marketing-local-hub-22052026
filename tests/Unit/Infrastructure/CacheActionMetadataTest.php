<?php

use Modules\AdminCache\Support\CacheActionRegistry;

test('cache action metadata explains what each action does and when to use it', function (): void {
    $registry = app(CacheActionRegistry::class);
    $actions = collect($registry->gridItems())
        ->push($registry->optimizeItem())
        ->push($registry->sessionItem())
        ->keyBy('key');

    expect($actions->keys()->all())->toEqualCanonicalizing([
        'app',
        'config',
        'route',
        'view',
        'optimize',
        'session',
    ]);

    foreach ($actions as $action) {
        expect($action)
            ->toHaveKeys(['description', 'when', 'after'])
            ->and($action['description'])->not->toBe('')
            ->and($action['when'])->not->toBe('')
            ->and($action['after'])->not->toBe('');
    }

    expect($actions['route']['button'])->toBe('Managed')
        ->and($actions['route']['when'])->toContain('redeploy')
        ->and($actions['optimize']['description'])->toContain('without rebuilding routes')
        ->and($actions['app']['after'])->toContain('next request')
        ->and($actions['config']['when'])->toContain('environment')
        ->and($actions['view']['when'])->toContain('Blade')
        ->and($actions['session']['after'])->toContain('login');
});
