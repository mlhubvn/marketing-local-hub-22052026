<?php

test('license purchase codes are not committed as configuration defaults', function (): void {
    expect(config('mlhub.license.purchase_code'))->toBe('');
});

test('marketplace seed metadata does not contain purchase codes', function (): void {
    $packages = require database_path('seeders/data/mlhub_marketplace_packages.php');

    foreach ($packages as $package) {
        expect($package['purchase_code'] ?? null)->toBeNull();
    }
});
