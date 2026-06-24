<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Modules\AdminCache\Actions\ClearRouteCacheAction;
use Modules\AdminCache\Actions\OptimizeApplicationAction;
use Modules\CustomMLHUB\Support\MLHUBArtisanTasks;

test('container startup optimizes without rebuilding the route cache', function (): void {
    $entrypoint = file_get_contents(base_path('docker/entrypoint.sh'));

    expect($entrypoint)
        ->toContain("find bootstrap/cache -maxdepth 1 -name 'routes*.php' -delete")
        ->toContain('php artisan optimize --except=routes --ansi');
});

test('admin optimize skips route cache rebuilds while the application is live', function (): void {
    Artisan::shouldReceive('call')
        ->once()
        ->with('optimize', ['--except' => 'routes'])
        ->andReturn(0);

    $message = app(OptimizeApplicationAction::class)->handle();

    expect($message)->toBe(__('Application optimized successfully. Route cache was skipped to keep scheduled tasks stable.'));
});

test('admin route cache action does not delete route cache while the application is live', function (): void {
    Artisan::shouldReceive('call')->never();

    $message = app(ClearRouteCacheAction::class)->handle();

    expect($message)->toBe(__('Route cache is managed during deployment and is not cleared while the application is live.'));
});

test('custom mlhub optimize tasks skip route cache rebuilds', function (): void {
    $command = Mockery::mock(Command::class);
    $command->shouldReceive('call')->once()->with('optimize:clear', ['--except' => 'routes'])->andReturn(0);
    $command->shouldReceive('call')->once()->with('optimize', ['--except' => 'routes'])->andReturn(0);

    MLHUBArtisanTasks::optimize($command);
});

test('marketplace package cache refreshes skip live route cache mutation', function (): void {
    $service = file_get_contents(base_path('modules/AdminMarketplace/Services/MarketplacePackageService.php'));

    expect($service)
        ->not->toContain("Artisan::call('optimize:clear');")
        ->toContain("Artisan::call('optimize:clear', ['--except' => 'routes']);");
});
