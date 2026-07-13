<?php

use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerApiLog;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerOneTimeLogin;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

function createFizaHUBPartnerParentTables(): void
{
    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->string('email')->nullable();
        $table->timestamps();
    });

    Schema::create('teams', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->unsignedBigInteger('owner_user_id')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_businesses', function (Blueprint $table): void {
        $table->id();
        $table->string('name')->nullable();
        $table->unsignedBigInteger('user_id')->nullable();
        $table->timestamps();
    });

    Schema::create('support_tickets', function (Blueprint $table): void {
        $table->id();
        $table->string('subject')->nullable();
    });
}

function migrateFizaHUBPartnerTables(): void
{
    $migration = require base_path('modules/APIPartnerFizaHUB/Database/Migrations/2026_07_13_000000_create_fizahub_partner_api_tables.php');
    $migration->up();
}

beforeEach(function (): void {
    Schema::dropIfExists('partner_one_time_logins');
    Schema::dropIfExists('partner_api_logs');
    Schema::dropIfExists('partner_onboarding_requests');
    Schema::dropIfExists('partner_integrations');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');

    createFizaHUBPartnerParentTables();
    migrateFizaHUBPartnerTables();
});

afterEach(function (): void {
    Schema::dropIfExists('partner_one_time_logins');
    Schema::dropIfExists('partner_api_logs');
    Schema::dropIfExists('partner_onboarding_requests');
    Schema::dropIfExists('partner_integrations');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
});

test('partner tables expose required columns', function (): void {
    expect(Schema::hasColumns('partner_integrations', [
        'id', 'partner_code', 'external_business_id', 'external_user_id',
        'mlhub_user_id', 'mlhub_workspace_id', 'mlhub_business_id',
        'package_code', 'status', 'verification_status', 'metadata',
        'created_at', 'updated_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('partner_onboarding_requests', [
        'id', 'partner_code', 'request_id', 'external_business_id', 'external_user_id',
        'package_code', 'status', 'current_step', 'payload', 'verification_status',
        'duplicate_check', 'support_ticket_id', 'mlhub_user_id', 'mlhub_workspace_id',
        'mlhub_business_id', 'created_at', 'updated_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('partner_api_logs', [
        'id', 'partner_code', 'method', 'endpoint', 'request_id', 'idempotency_key',
        'request_hash', 'status_code', 'request_payload', 'response_payload',
        'created_at', 'updated_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('partner_one_time_logins', [
        'id', 'partner_integration_id', 'user_id', 'request_id', 'token_hash',
        'expires_at', 'used_at', 'created_at', 'updated_at',
    ]))->toBeTrue();
});

test('partner models cast JSON datetime and integer fields', function (): void {
    $user = User::query()->create(['name' => 'Owner', 'email' => 'owner@example.com']);
    $team = Team::query()->create(['name' => 'Workspace', 'owner_user_id' => $user->id]);
    $business = LocalBusiness::query()->create(['name' => 'Shop', 'user_id' => $user->id]);

    $integration = PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'biz-1',
        'external_user_id' => 'user-1',
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'package_code' => 'base',
        'status' => 'active',
        'verification_status' => ['identity_verified' => true],
        'metadata' => ['tax_code' => '0101234567'],
    ]);

    $onboarding = PartnerOnboardingRequest::query()->create([
        'partner_code' => 'fizahub',
        'request_id' => (string) str()->uuid(),
        'external_business_id' => 'biz-1',
        'package_code' => 'base',
        'status' => 'completed',
        'current_step' => 'ready',
        'payload' => ['business' => ['name' => 'Shop']],
        'verification_status' => ['identity_verified' => true],
        'duplicate_check' => [],
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
    ]);

    $login = PartnerOneTimeLogin::query()->create([
        'partner_integration_id' => $integration->id,
        'user_id' => $user->id,
        'request_id' => (string) str()->uuid(),
        'token_hash' => hash('sha256', 'plain-token'),
        'expires_at' => now()->addMinutes(5),
        'used_at' => null,
    ]);

    $log = PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'GET',
        'endpoint' => '/api/v1/partners/fizahub/health',
        'request_id' => (string) str()->uuid(),
        'idempotency_key' => null,
        'request_hash' => hash('sha256', '{}'),
        'status_code' => 200,
        'request_payload' => ['body' => []],
        'response_payload' => ['success' => true],
    ]);

    $integration->refresh();
    $onboarding->refresh();
    $login->refresh();
    $log->refresh();

    expect($integration->verification_status)->toBeArray()
        ->and($integration->metadata)->toBeArray()
        ->and($integration->mlhub_user_id)->toBeInt()
        ->and($onboarding->payload)->toBeArray()
        ->and($onboarding->duplicate_check)->toBeArray()
        ->and($log->request_payload)->toBeArray()
        ->and($log->response_payload)->toBeArray()
        ->and($log->status_code)->toBeInt()
        ->and($login->expires_at)->toBeInstanceOf(CarbonInterface::class);
});

test('partner model relationships resolve mapped records', function (): void {
    $user = User::query()->create(['name' => 'Owner', 'email' => 'owner2@example.com']);
    $team = Team::query()->create(['name' => 'Workspace', 'owner_user_id' => $user->id]);
    $business = LocalBusiness::query()->create(['name' => 'Shop', 'user_id' => $user->id]);

    $integration = PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'biz-rel',
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'status' => 'active',
    ]);

    PartnerOneTimeLogin::query()->create([
        'partner_integration_id' => $integration->id,
        'user_id' => $user->id,
        'request_id' => (string) str()->uuid(),
        'token_hash' => hash('sha256', 'rel-token'),
        'expires_at' => now()->addMinutes(5),
    ]);

    expect($integration->user->is($user))->toBeTrue()
        ->and($integration->workspace->is($team))->toBeTrue()
        ->and($integration->business->is($business))->toBeTrue()
        ->and($integration->oneTimeLogins)->toHaveCount(1);
});

test('partner uniqueness constraints are enforced', function (): void {
    $user = User::query()->create(['name' => 'Owner', 'email' => 'owner3@example.com']);
    $team = Team::query()->create(['name' => 'Workspace', 'owner_user_id' => $user->id]);
    $business = LocalBusiness::query()->create(['name' => 'Shop', 'user_id' => $user->id]);

    PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'biz-unique',
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'status' => 'active',
    ]);

    expect(fn () => PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'biz-unique',
        'status' => 'active',
    ]))->toThrow(QueryException::class);

    $requestId = (string) str()->uuid();

    PartnerOnboardingRequest::query()->create([
        'partner_code' => 'fizahub',
        'request_id' => $requestId,
        'external_business_id' => 'biz-unique',
        'package_code' => 'base',
        'status' => 'pending_verification',
        'current_step' => 'verification',
    ]);

    expect(fn () => PartnerOnboardingRequest::query()->create([
        'partner_code' => 'fizahub',
        'request_id' => $requestId,
        'external_business_id' => 'biz-unique-2',
        'package_code' => 'base',
        'status' => 'pending_verification',
        'current_step' => 'verification',
    ]))->toThrow(QueryException::class);

    $integration = PartnerIntegration::query()->firstOrFail();
    $tokenHash = hash('sha256', 'same-token');

    PartnerOneTimeLogin::query()->create([
        'partner_integration_id' => $integration->id,
        'user_id' => $user->id,
        'request_id' => (string) str()->uuid(),
        'token_hash' => $tokenHash,
        'expires_at' => now()->addMinutes(5),
    ]);

    expect(fn () => PartnerOneTimeLogin::query()->create([
        'partner_integration_id' => $integration->id,
        'user_id' => $user->id,
        'request_id' => (string) str()->uuid(),
        'token_hash' => $tokenHash,
        'expires_at' => now()->addMinutes(5),
    ]))->toThrow(QueryException::class);

    PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'POST',
        'endpoint' => '/api/v1/partners/fizahub/onboarding-requests',
        'request_id' => (string) str()->uuid(),
        'idempotency_key' => 'idem-1',
        'request_hash' => hash('sha256', '{"a":1}'),
        'status_code' => 201,
        'request_payload' => [],
        'response_payload' => [],
    ]);

    expect(fn () => PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'POST',
        'endpoint' => '/api/v1/partners/fizahub/onboarding-requests',
        'request_id' => (string) str()->uuid(),
        'idempotency_key' => 'idem-1',
        'request_hash' => hash('sha256', '{"a":2}'),
        'status_code' => 201,
        'request_payload' => [],
        'response_payload' => [],
    ]))->toThrow(QueryException::class);

    PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'GET',
        'endpoint' => '/api/v1/partners/fizahub/health',
        'request_id' => (string) str()->uuid(),
        'idempotency_key' => null,
        'status_code' => 200,
    ]);

    PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'GET',
        'endpoint' => '/api/v1/partners/fizahub/health',
        'request_id' => (string) str()->uuid(),
        'idempotency_key' => null,
        'status_code' => 200,
    ]);

    expect(PartnerApiLog::query()->whereNull('idempotency_key')->count())->toBe(2);
});
