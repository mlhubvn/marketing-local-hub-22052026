<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once __DIR__.'/FizaHubTestHelpers.php';

/**
 * `GET /health` (Phase 4b) doubles as a readiness probe checking database, partner_schema,
 * default_plan, and support_tables. Any critical dependency missing must return 503 degraded
 * instead of a false-positive 200 ok — this is exactly the deploy-readiness gap that caused
 * the production onboarding 500 (see RootCauseInvestigationTest).
 */
function readinessHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Accept' => 'application/json',
    ], $overrides);
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);

    dropFizaHubReadinessSchema();
});

afterEach(function (): void {
    dropFizaHubReadinessSchema();
});

test('health reports degraded 503 with no schema deployed at all', function (): void {
    $response = $this->getJson('/api/v1/partners/fizahub/health', readinessHeaders());

    $response->assertStatus(503)
        ->assertJsonPath('data.status', 'degraded')
        ->assertJsonPath('data.checks.partner_schema.status', 'degraded')
        ->assertJsonPath('data.checks.default_plan.status', 'degraded')
        ->assertJsonPath('data.checks.support_tables.status', 'degraded');

    expect($response->json('data.checks.database.status'))->toBe('ok');
});

test('health reports ok 200 once the full readiness schema and default plan are deployed', function (): void {
    bootFizaHubReadinessSchema();

    $response = $this->getJson('/api/v1/partners/fizahub/health', readinessHeaders());

    $response->assertOk()
        ->assertJsonPath('data.status', 'ok')
        ->assertJsonPath('data.checks.database.status', 'ok')
        ->assertJsonPath('data.checks.partner_schema.status', 'ok')
        ->assertJsonPath('data.checks.default_plan.status', 'ok')
        ->assertJsonPath('data.checks.support_tables.status', 'ok');
});

test('health reports degraded 503 when only the first migration ran and the plan/support tables are missing', function (): void {
    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->timestamps();
    });
    Schema::create('teams', function (Blueprint $table): void {
        $table->id();
        $table->timestamps();
    });
    Schema::create('lb_businesses', function (Blueprint $table): void {
        $table->id();
        $table->timestamps();
    });
    Schema::create('support_tickets', function (Blueprint $table): void {
        $table->id();
        $table->timestamps();
    });

    $migration = require base_path('modules/APIPartnerFizaHUB/Database/Migrations/2026_07_13_000000_create_fizahub_partner_api_tables.php');
    $migration->up();

    $response = $this->getJson('/api/v1/partners/fizahub/health', readinessHeaders());

    $response->assertStatus(503)
        ->assertJsonPath('data.status', 'degraded')
        ->assertJsonPath('data.checks.default_plan.status', 'degraded')
        ->assertJsonPath('data.checks.support_tables.status', 'degraded');

    expect($response->json('data.checks.partner_schema.status'))->toBe('degraded')
        ->and($response->json('data.checks.partner_schema.message'))->toContain('partner_onboarding_status_histories')
        ->and($response->json('data.checks.partner_schema.message'))->toContain('requested_package_code');

    Schema::dropIfExists('partner_one_time_logins');
    Schema::dropIfExists('partner_api_logs');
    Schema::dropIfExists('partner_onboarding_requests');
    Schema::dropIfExists('partner_integrations');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
});

test('health reports degraded 503 when schema is ready but the default plan is missing or inactive', function (): void {
    bootFizaHubReadinessSchema();
    DB::table('plans')->where('slug', 'mlhub-free-da-nang')->update(['status' => false]);

    $response = $this->getJson('/api/v1/partners/fizahub/health', readinessHeaders());

    $response->assertStatus(503)
        ->assertJsonPath('data.status', 'degraded')
        ->assertJsonPath('data.checks.partner_schema.status', 'ok')
        ->assertJsonPath('data.checks.support_tables.status', 'ok');

    expect($response->json('data.checks.default_plan.status'))->toBe('degraded')
        ->and($response->json('data.checks.default_plan.message'))->toContain('mlhub-free-da-nang');
});
