<?php

use Modules\AdminLog\Support\LogManager;

test('log deletion reports a filesystem failure instead of success', function (): void {
    $manager = new class extends LogManager
    {
        public function resolve(string $file): string
        {
            return storage_path('logs');
        }
    };

    expect(fn () => $manager->delete('laravel.log'))
        ->toThrow(RuntimeException::class, 'could not be deleted');
});
