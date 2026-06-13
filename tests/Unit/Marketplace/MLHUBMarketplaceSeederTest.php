<?php

use Database\Seeders\MLHUBMarketplaceSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('update mode preserves administrator-owned marketplace activation state', function (): void {
    Schema::create('marketplace_packages', function (Blueprint $table): void {
        $table->id();
        $table->string('package_key')->unique();
        $table->string('id_secure')->nullable();
        $table->string('module_name')->nullable();
        $table->string('title')->nullable();
        $table->text('description')->nullable();
        $table->string('version')->nullable();
        $table->string('source_type')->nullable();
        $table->unsignedBigInteger('product_id')->nullable();
        $table->string('purchase_code')->nullable();
        $table->string('product_slug')->nullable();
        $table->string('license_type')->nullable();
        $table->string('licensed_domain')->nullable();
        $table->string('install_path')->nullable();
        $table->json('providers')->nullable();
        $table->json('meta')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamp('installed_at')->nullable();
        $table->timestamp('last_synced_at')->nullable();
        $table->timestamps();
    });

    $packages = require database_path('seeders/data/mlhub_marketplace_packages.php');
    $package = (array) $packages[0];

    DB::table('marketplace_packages')->insert([
        'package_key' => $package['package_key'],
        'is_active' => false,
    ]);

    config(['mlhub.seeding_mode' => 'update']);

    (new MLHUBMarketplaceSeeder)->run();

    expect((bool) DB::table('marketplace_packages')
        ->where('package_key', $package['package_key'])
        ->value('is_active'))->toBeFalse();
});
