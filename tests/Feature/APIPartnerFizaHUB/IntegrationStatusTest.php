<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

function createIntegrationStatusTables(): void
{
    Schema::dropIfExists('partner_one_time_logins');
    Schema::dropIfExists('partner_api_logs');
    Schema::dropIfExists('partner_onboarding_requests');
    Schema::dropIfExists('partner_integrations');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('support_tickets');

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('username')->nullable()->unique();
        $table->string('email')->unique();
        $table->string('locale', 10)->nullable();
        $table->string('timezone', 100)->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->boolean('is_super_admin')->default(false);
        $table->timestamps();
    });

    Schema::create('teams', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug');
        $table->unsignedBigInteger('owner_user_id')->nullable();
        $table->timestamps();
    });

    Schema::create('team_user', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id');
        $table->unsignedBigInteger('user_id');
        $table->timestamps();
    });

    Schema::create('lb_businesses', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('name');
        $table->string('type', 60)->default('other');
        $table->timestamps();
    });

    Schema::create('support_tickets', function (Blueprint $table): void {
        $table->id();
        $table->string('id_secure', 40)->unique();
        $table->unsignedBigInteger('uid');
        $table->unsignedBigInteger('open_by');
        $table->unsignedBigInteger('team_id')->nullable();
        $table->unsignedTinyInteger('status')->default(1);
        $table->string('title', 255);
        $table->text('content');
        $table->timestamps();
    });

    createFizaHubPartnerTables();

    // Mirror the extend migration's onboarding columns since createFizaHubPartnerTables()
    // already applies them via FizaHubTestHelpers.
}

function integrationStatusHeaders(): array
{
    return [
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Accept' => 'application/json',
    ];
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);
    createIntegrationStatusTables();
});

afterEach(function (): void {
    dropFizaHubPartnerTables();
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
});

test('integration status returns 404 integration_not_found for an unmapped business', function (): void {
    $this->getJson(
        '/api/v1/partners/fizahub/businesses/unknown-biz/integration-status',
        integrationStatusHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'integration_not_found')
        ->assertJsonPath('error.details.next_action', 'create_onboarding_request');
});

test('integration status returns mapping, onboarding status and package for a mapped business', function (): void {
    $user = User::query()->create([
        'name' => 'Status Owner',
        'username' => 'status_'.Str::lower(Str::random(8)),
        'email' => 'status-owner@example.com',
        'password' => 'password-password-password-password-password-1234',
    ]);

    $team = Team::query()->create([
        'name' => 'Status Team',
        'slug' => 'status-team',
        'owner_user_id' => $user->id,
    ]);

    $business = LocalBusiness::query()->create([
        'user_id' => $user->id,
        'name' => 'Status Store',
        'type' => 'restaurant',
    ]);

    $integration = PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'biz-status',
        'external_user_id' => 'ext-status',
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'package_code' => 'free',
        'status' => 'active',
    ]);

    PartnerOnboardingRequest::query()->create([
        'partner_code' => 'fizahub',
        'request_id' => (string) Str::uuid(),
        'external_business_id' => 'biz-status',
        'external_user_id' => 'ext-status',
        'package_code' => 'free',
        'requested_package_code' => 'free',
        'approved_package_code' => 'free',
        'status' => OnboardingStatusMachine::READY,
        'current_step' => OnboardingStatusMachine::defaultStepFor(OnboardingStatusMachine::READY),
        'admin_status' => OnboardingStatusMachine::READY,
        'payload' => [],
        'verification_status' => [],
        'duplicate_check' => [],
    ]);

    $response = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-status/integration-status',
        integrationStatusHeaders()
    )->assertOk();

    $response->assertJsonPath('data.external_business_id', 'biz-status')
        ->assertJsonPath('data.integration_status', 'active')
        ->assertJsonPath('data.mlhub_user_id', $user->id)
        ->assertJsonPath('data.onboarding.status', OnboardingStatusMachine::READY)
        ->assertJsonPath('data.package.package_code', 'free');

    expect($integration->fresh())->not->toBeNull();
});

test('integration status for another business external id never leaks a different tenant mapping', function (): void {
    $userA = User::query()->create([
        'name' => 'Tenant A',
        'username' => 'tenant_a_'.Str::lower(Str::random(6)),
        'email' => 'tenant-a@example.com',
        'password' => 'password-password-password-password-password-1234',
    ]);
    $teamA = Team::query()->create(['name' => 'Team A', 'slug' => 'team-a', 'owner_user_id' => $userA->id]);
    $businessA = LocalBusiness::query()->create(['user_id' => $userA->id, 'name' => 'Business A', 'type' => 'other']);

    PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'biz-tenant-a',
        'mlhub_user_id' => $userA->id,
        'mlhub_workspace_id' => $teamA->id,
        'mlhub_business_id' => $businessA->id,
        'package_code' => 'free',
        'status' => 'active',
    ]);

    // biz-tenant-b was never onboarded.
    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-tenant-b/integration-status',
        integrationStatusHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'integration_not_found')
        ->assertJsonPath('data', null);
});
