<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

function marketingBootstrapHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) Str::uuid(),
        'Idempotency-Key' => (string) Str::uuid(),
        'Accept' => 'application/json',
    ], $overrides);
}

/** @return array{integration: PartnerIntegration, onboarding: PartnerOnboardingRequest, user: User, business: LocalBusiness} */
function seedMarketingBootstrapBusiness(string $status = OnboardingStatusMachine::AWAITING_CONSULTANT): array
{
    $plan = AdminPlan::query()->firstOrCreate(['slug' => 'mlhub-free-da-nang'], [
        'name' => 'MLHUB Free Da Nang',
        'status' => true,
        'free_plan' => true,
        'default_signup_plan' => true,
        'currency' => 'VND',
        'price' => 0,
        'permissions' => [],
    ]);
    $user = User::query()->create([
        'name' => 'Marketing Owner',
        'username' => 'marketingowner',
        'email' => 'marketing-owner@example.com',
        'password' => 'not-used-by-test',
        'plan_id' => $plan->id,
    ]);
    $team = Team::query()->create([
        'name' => 'Marketing Team',
        'slug' => 'marketing-team',
        'owner_user_id' => $user->id,
    ]);
    $business = LocalBusiness::query()->create([
        'user_id' => $user->id,
        'name' => 'Marketing Store',
        'type' => 'Restaurant',
        'industry_group_code' => 'food_beverage',
        'industry_category_code' => 'restaurant_eatery',
    ]);
    $integration = PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'biz-marketing',
        'external_user_id' => 'user-marketing',
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'package_code' => 'free',
        'status' => 'active',
        'metadata' => ['marketing_goal_codes' => ['local_presence']],
    ]);
    $onboarding = PartnerOnboardingRequest::query()->create([
        'partner_code' => 'fizahub',
        'request_id' => (string) Str::uuid(),
        'external_business_id' => 'biz-marketing',
        'external_user_id' => 'user-marketing',
        'package_code' => 'free',
        'requested_package_code' => 'free',
        'status' => $status,
        'current_step' => OnboardingStatusMachine::defaultStepFor($status),
        'admin_status' => $status,
        'payload' => ['marketing_goal_codes' => ['local_presence']],
        'verification_status' => [],
        'duplicate_check' => [],
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
    ]);

    return compact('integration', 'onboarding', 'user', 'business');
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);
    bootProductionLikeSchema();
});

afterEach(function (): void {
    dropFizaHubPartnerTables();
    Schema::dropIfExists('affiliate_profiles');
    Schema::dropIfExists('support_comments');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');
});

test('unknown business marketing status returns the onboarding next action', function (): void {
    $this->getJson(
        '/api/v1/partners/fizahub/businesses/missing/marketing-status',
        marketingBootstrapHeaders()
    )->assertNotFound()
        ->assertJsonPath('error.code', 'integration_not_found')
        ->assertJsonPath('error.details.next_action', 'create_onboarding_request');
});

test('marketing status exposes stable activation onboarding capabilities and links', function (): void {
    $seed = seedMarketingBootstrapBusiness(OnboardingStatusMachine::READY);

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-marketing/marketing-status',
        marketingBootstrapHeaders()
    )->assertOk()
        ->assertJsonPath('data.external_business_id', 'biz-marketing')
        ->assertJsonPath('data.activation_status', 'active')
        ->assertJsonPath('data.onboarding_status', 'ready')
        ->assertJsonPath('data.is_ready', true)
        ->assertJsonPath('data.effective_package_code', 'free')
        ->assertJsonPath('data.requested_package_code', 'free')
        ->assertJsonPath('data.onboarding_request_id', $seed['onboarding']->request_id)
        ->assertJsonPath('data.mlhub_user_id', $seed['user']->id)
        ->assertJsonPath('data.capabilities.dashboard', true)
        ->assertJsonPath('data.capabilities.support', true)
        ->assertJsonPath('data.capabilities.crm', true)
        ->assertJsonPath('data.links.dashboard', '/api/v1/partners/fizahub/businesses/biz-marketing/dashboard');
});

test('marketing catalog provides goals industries and enriched packages in one request', function (): void {
    AdminPlan::query()->create([
        'name' => 'MLHUB Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'free_plan' => true,
        'default_signup_plan' => true,
        'currency' => 'VND',
        'price' => 0,
        'permissions' => [],
    ]);

    $this->getJson(
        '/api/v1/partners/fizahub/marketing-catalog?industry=restaurant_food',
        marketingBootstrapHeaders()
    )->assertOk()
        ->assertJsonPath('data.max_goal_selection', 3)
        ->assertJsonPath('data.default_package_code', 'free')
        ->assertJson(fn (AssertableJson $json) => $json
            ->has('data.marketing_goals', 4)
            ->has('data.industries')
            ->has('data.packages')
            ->whereType('data.packages.0.description', 'string')
            ->whereType('data.packages.0.features', 'array')
            ->whereType('data.packages.0.recommended_goal_codes', 'array')
            ->whereType('data.packages.0.industry_codes', 'array')
            ->etc());
});

test('preferences change requested package and goals without changing effective package', function (): void {
    $seed = seedMarketingBootstrapBusiness();

    $this->patchJson(
        '/api/v1/partners/fizahub/businesses/biz-marketing/marketing-preferences',
        [
            'marketing_goal_codes' => ['qr_checkin', 'customer_retention'],
            'requested_package_code' => 'base',
        ],
        marketingBootstrapHeaders(['Idempotency-Key' => 'preferences-1'])
    )->assertOk()
        ->assertJsonPath('data.marketing_goal_codes', ['qr_checkin', 'customer_retention'])
        ->assertJsonPath('data.requested_package_code', 'base')
        ->assertJsonPath('data.effective_package_code', 'free');

    expect($seed['integration']->refresh()->package_code)->toBe('free')
        ->and($seed['integration']->metadata['marketing_goal_codes'])->toBe(['qr_checkin', 'customer_retention'])
        ->and($seed['integration']->metadata['requested_package_code'])->toBe('base')
        ->and($seed['onboarding']->refresh()->requested_package_code)->toBe('base')
        ->and($seed['onboarding']->payload['marketing_goal_codes'])->toBe(['qr_checkin', 'customer_retention']);
});

test('preferences validate configured goals selection count and requested package', function (): void {
    seedMarketingBootstrapBusiness();

    $this->patchJson(
        '/api/v1/partners/fizahub/businesses/biz-marketing/marketing-preferences',
        [
            'marketing_goal_codes' => ['local_presence', 'qr_checkin', 'voucher_return', 'customer_retention'],
            'requested_package_code' => 'enterprise',
        ],
        marketingBootstrapHeaders()
    )->assertUnprocessable()
        ->assertJsonPath('error.code', 'validation_failed');
});
