<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once __DIR__.'/FizaHubTestHelpers.php';

/**
 * `php artisan fizahub:doctor` (Phase 4c) — same PASS/FAIL checklist a MLHUB ops person
 * would run right after a deploy: token, migrations, tables/columns, default plan, routes.
 */
beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');

    dropFizaHubReadinessSchema();
    Schema::dropIfExists('migrations');

    Schema::create('migrations', function (Illuminate\Database\Schema\Blueprint $table): void {
        $table->id();
        $table->string('migration');
        $table->integer('batch');
    });
});

afterEach(function (): void {
    dropFizaHubReadinessSchema();
    Schema::dropIfExists('migrations');
});

test('doctor fails fast with clear reasons when nothing is deployed', function (): void {
    config()->set('modules.apipartnerfizahub.token', '');

    $exitCode = Artisan::call('fizahub:doctor');
    $output = Artisan::output();

    expect($exitCode)->toBe(1)
        ->and($output)->toContain('[FAIL] token')
        ->and($output)->toContain('[FAIL] migrations')
        ->and($output)->toContain('[FAIL] partner_schema')
        ->and($output)->toContain('[FAIL] default_plan')
        ->and($output)->toContain('[FAIL] support_tables')
        ->and($output)->toContain('OVERALL: FAIL');
});

test('doctor passes everything once the module is fully migrated, seeded, and routed', function (): void {
    DB::table('migrations')->insert([
        ['migration' => '2026_07_13_000000_create_fizahub_partner_api_tables', 'batch' => 1],
        ['migration' => '2026_07_17_120000_extend_fizahub_partner_onboarding_tables', 'batch' => 1],
    ]);

    bootFizaHubReadinessSchema();

    $exitCode = Artisan::call('fizahub:doctor');
    $output = Artisan::output();

    expect($exitCode)->toBe(0)
        ->and($output)->toContain('[PASS] token')
        ->and($output)->toContain('[PASS] migrations')
        ->and($output)->toContain('[PASS] database')
        ->and($output)->toContain('[PASS] partner_schema')
        ->and($output)->toContain('[PASS] default_plan')
        ->and($output)->toContain('[PASS] support_tables')
        ->and($output)->toContain('[PASS] routes')
        ->and($output)->toContain('OVERALL: PASS');
});

test('doctor reports migrations ran but plan missing as two distinct failures', function (): void {
    DB::table('migrations')->insert([
        ['migration' => '2026_07_13_000000_create_fizahub_partner_api_tables', 'batch' => 1],
        ['migration' => '2026_07_17_120000_extend_fizahub_partner_onboarding_tables', 'batch' => 1],
    ]);

    bootFizaHubReadinessSchema();
    DB::table('plans')->where('slug', 'mlhub-free-da-nang')->delete();

    $exitCode = Artisan::call('fizahub:doctor');
    $output = Artisan::output();

    expect($exitCode)->toBe(1)
        ->and($output)->toContain('[PASS] migrations')
        ->and($output)->toContain('[PASS] partner_schema')
        ->and($output)->toContain('[FAIL] default_plan')
        ->and($output)->toContain('OVERALL: FAIL');
});
