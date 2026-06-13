<?php

use Database\Seeders\PlanSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('starter plans do not gain google business access from the generic business limit', function (): void {
    $seeder = new class extends PlanSeeder
    {
        public function normalizedStarterPermissions(): array
        {
            return $this->normalizePermissions($this->starterPermissions());
        }
    };

    expect($seeder->normalizedStarterPermissions()['google_business'])->toBeFalse();
});

test('update mode preserves existing administrator-owned plan values', function (): void {
    Schema::create('plans', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug')->unique();
        $table->boolean('status')->default(true);
        $table->boolean('featured')->default(false);
        $table->string('currency')->default('VND');
        $table->decimal('price', 16, 2)->default(0);
        $table->unsignedTinyInteger('type')->default(1);
        $table->boolean('free_plan')->default(false);
        $table->boolean('default_signup_plan')->default(false);
        $table->unsignedInteger('trial_day')->default(0);
        $table->integer('position')->default(0);
        $table->text('desc')->nullable();
        $table->json('permissions')->nullable();
        $table->timestamps();
    });

    DB::table('plans')->insert([
        'name' => 'Custom Starter',
        'slug' => 'starter-monthly',
        'status' => false,
        'currency' => 'USD',
        'price' => 123,
        'permissions' => json_encode([
            'google_business' => false,
            'max_google_business_connections' => 2,
        ]),
    ]);

    config(['mlhub.seeding_mode' => 'update']);

    (new PlanSeeder)->run();

    expect(DB::table('plans')->where('slug', 'starter-monthly')->first())
        ->name->toBe('Custom Starter')
        ->status->toBe(0)
        ->currency->toBe('USD')
        ->price->toBe(123)
        ->and(json_decode((string) DB::table('plans')->where('slug', 'starter-monthly')->value('permissions'), true))
        ->google_business->toBeFalse()
        ->max_google_business_connections->toBe(2)
        ->credits_usage->toBeTrue();
});
