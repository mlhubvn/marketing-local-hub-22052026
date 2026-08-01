<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

function createPackageApiTables(): void
{
    Schema::dropIfExists('partner_one_time_logins');
    Schema::dropIfExists('partner_api_logs');
    Schema::dropIfExists('partner_onboarding_requests');
    Schema::dropIfExists('partner_integrations');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');

    Schema::create('support_tickets', function (Blueprint $table): void {
        $table->id();
        $table->string('subject')->nullable();
        $table->timestamps();
    });

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

    Schema::create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('username')->nullable()->unique();
        $table->string('email')->unique();
        $table->string('locale', 10)->nullable();
        $table->string('timezone', 100)->nullable();
        $table->unsignedBigInteger('plan_id')->nullable();
        $table->timestamp('plan_started_at')->nullable();
        $table->timestamp('plan_expires_at')->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('referral_code', 20)->nullable();
        $table->boolean('is_super_admin')->default(false);
        $table->timestamps();
    });

    Schema::create('teams', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug');
        $table->text('description')->nullable();
        $table->unsignedBigInteger('owner_user_id')->nullable();
        $table->timestamps();
    });

    Schema::create('team_user', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id');
        $table->unsignedBigInteger('user_id');
        $table->string('role', 50)->default('member');
        $table->json('permissions')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_businesses', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('name');
        $table->string('type', 60)->default('other');
        $table->timestamps();
    });

    createFizaHubPartnerTables();
}

/**
 * @return array{user: User, integration: PartnerIntegration, plan: AdminPlan}
 */
function seedPackageBusiness(): array
{
    $plan = AdminPlan::query()->create([
        'name' => 'MKT Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'free_plan' => true,
        'currency' => 'VND',
        'price' => 199000,
        'trial_day' => 7,
        'permissions' => [
            'max_businesses' => 1,
            'max_campaigns' => 5,
            'max_landing_pages' => 5,
            'max_qr_codes' => 10,
            'max_team_members' => 3,
            'admin_panel' => true,
            'credit_balance_view' => true,
            'payment_history' => true,
            'full_permissions_dump' => ['a', 'b'],
        ],
    ]);

    $user = User::query()->create([
        'name' => 'Package Owner',
        'username' => 'pkg_'.Str::lower(Str::random(8)),
        'email' => 'package@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'plan_id' => $plan->id,
        'plan_started_at' => now()->subDay(),
        'plan_expires_at' => now()->addDays(30),
        'locale' => 'vi',
        'timezone' => 'Asia/Ho_Chi_Minh',
    ]);

    $team = Team::query()->create([
        'name' => 'Package Team',
        'slug' => 'team-package',
        'owner_user_id' => $user->id,
    ]);

    $business = LocalBusiness::query()->create([
        'user_id' => $user->id,
        'name' => 'Package Shop',
        'type' => 'Restaurant',
    ]);

    $integration = PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'biz-package',
        'external_user_id' => 'ext-package',
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'package_code' => 'base',
        'status' => 'active',
    ]);

    PartnerOnboardingRequest::query()->create([
        'partner_code' => 'fizahub',
        'request_id' => (string) Str::uuid(),
        'external_business_id' => 'biz-package',
        'external_user_id' => 'ext-package',
        'package_code' => 'base',
        'requested_package_code' => 'base',
        'approved_package_code' => null,
        'status' => OnboardingStatusMachine::READY,
        'current_step' => OnboardingStatusMachine::defaultStepFor(OnboardingStatusMachine::READY),
        'admin_status' => OnboardingStatusMachine::READY,
        'payload' => [],
        'verification_status' => [],
        'duplicate_check' => [],
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
    ]);

    return compact('user', 'integration', 'plan');
}

function packageHeaders(array $overrides = []): array
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
    createPackageApiTables();
});

afterEach(function (): void {
    dropFizaHubPartnerTables();
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');
    Schema::dropIfExists('support_tickets');
});

test('package api maps base package to mlhub-free-da-nang with whitelisted limits only', function (): void {
    seedPackageBusiness();

    $response = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-package/package',
        packageHeaders()
    )->assertOk();

    $data = $response->json('data');

    expect($data['effective_package'])->toBe('base')
        ->and($data['requested_package'])->toBe('base')
        ->and($data['approved_package'])->toBeNull()
        ->and($data['package_name'])->toBe('MKT Free Da Nang')
        ->and($data['plan_slug'])->toBe('mlhub-free-da-nang')
        ->and($data['status'])->toBe('active')
        ->and($data['is_trial'])->toBeTrue()
        ->and($data['mapping_status'])->toBe('active')
        ->and($data['starts_at'])->toBeString()->not->toBeEmpty()
        ->and($data['expires_at'])->toBeString()->not->toBeEmpty()
        ->and($data['limits'])->toBe([
            'max_businesses' => 1,
            'max_campaigns' => 5,
            'max_landing_pages' => 5,
            'max_qr_codes' => 10,
            'max_team_members' => 3,
        ]);

    expect($data)->not->toHaveKey('price')
        ->and($data)->not->toHaveKey('currency')
        ->and($data)->not->toHaveKey('payment')
        ->and($data)->not->toHaveKey('subscription')
        ->and($data)->not->toHaveKey('credits')
        ->and($data)->not->toHaveKey('credit_balance')
        ->and($data)->not->toHaveKey('permissions')
        ->and($data['limits'])->not->toHaveKey('admin_panel')
        ->and($data['limits'])->not->toHaveKey('credit_balance_view')
        ->and($data['limits'])->not->toHaveKey('payment_history')
        ->and($data['limits'])->not->toHaveKey('full_permissions_dump');

    $encoded = json_encode($data);
    expect($encoded)->not->toContain('199000')
        ->and($encoded)->not->toContain('admin_panel')
        ->and($encoded)->not->toContain('credit_balance_view');
});

test('marketing catalog lists available packages with goals industries and a default free flag', function (): void {
    seedPackageBusiness();

    $response = $this->getJson(
        '/api/v1/partners/fizahub/marketing-catalog?industry=restaurant_food',
        packageHeaders()
    )->assertOk();

    $data = $response->json('data');

    expect($data['default_package_code'])->toBe('free')
        ->and($data['max_goal_selection'])->toBe(3)
        ->and($data['marketing_goals'])->toHaveCount(4)
        ->and($data['industries'])->not->toBeEmpty()
        ->and(collect($data['packages'])->pluck('package_code')->all())
        ->toContain('free')
        ->toContain('base');

    $free = collect($data['packages'])->firstWhere('package_code', 'free');
    expect($free['plan_slug'])->toBe('mlhub-free-da-nang')
        ->and($free['is_default'])->toBeTrue()
        ->and($free['is_free'])->toBeTrue()
        ->and($free['description'])->toBeString()
        ->and($free['features'])->toBeArray()
        ->and($free['recommended_goal_codes'])->toBeArray()
        ->and($free['industry_codes'])->toBeArray();
});

test('package api returns 404 for unmapped business', function (): void {
    $this->getJson(
        '/api/v1/partners/fizahub/businesses/unknown-package/package',
        packageHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'integration_not_found');
});
