<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('the configured owner is preserved as super admin without trusting a username', function (): void {
    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('email')->unique();
        $table->boolean('is_super_admin')->default(false);
    });

    DB::table('users')->insert([
        ['email' => 'owner@mlhub.vn', 'is_super_admin' => false],
        ['email' => 'other@mlhub.vn', 'is_super_admin' => false],
    ]);

    config(['custommlhub.first_user.email' => 'owner@mlhub.vn']);

    $migration = require database_path('migrations/2026_06_13_000000_preserve_configured_super_admin.php');
    $migration->up();

    expect((bool) DB::table('users')->where('email', 'owner@mlhub.vn')->value('is_super_admin'))->toBeTrue()
        ->and((bool) DB::table('users')->where('email', 'other@mlhub.vn')->value('is_super_admin'))->toBeFalse();
});
