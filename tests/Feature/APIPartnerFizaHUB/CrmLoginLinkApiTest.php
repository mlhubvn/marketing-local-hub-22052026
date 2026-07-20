<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerOneTimeLogin;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

/**
 * @return array{user: User, team: Team, integration: PartnerIntegration}
 */
function seedCrmBusiness(string $externalBusinessId, ?string $status): array
{
    $plan = AdminPlan::query()->firstOrCreate(
        ['slug' => 'mlhub-free-da-nang'],
        [
            'name' => 'MLHUB Free Da Nang',
            'status' => true,
            'free_plan' => true,
            'currency' => 'VND',
            'price' => 0,
            'permissions' => [],
        ]
    );
    $user = User::query()->create([
        'name' => 'CRM '.$externalBusinessId,
        'username' => 'crm'.Str::lower(Str::random(10)),
        'email' => $externalBusinessId.'@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'plan_id' => $plan->id,
        'is_super_admin' => false,
    ]);
    $team = Team::query()->create([
        'name' => 'CRM Team '.$externalBusinessId,
        'slug' => 'crm-team-'.$externalBusinessId,
        'owner_user_id' => $user->id,
    ]);
    $business = LocalBusiness::query()->create([
        'user_id' => $user->id,
        'name' => 'CRM Business '.$externalBusinessId,
        'type' => 'Restaurant',
    ]);
    $integration = PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => $externalBusinessId,
        'external_user_id' => 'ext-'.$externalBusinessId,
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'package_code' => 'free',
        'status' => 'active',
    ]);

    if ($status !== null) {
        PartnerOnboardingRequest::query()->create([
            'partner_code' => 'fizahub',
            'request_id' => (string) Str::uuid(),
            'external_business_id' => $externalBusinessId,
            'external_user_id' => 'ext-'.$externalBusinessId,
            'package_code' => 'free',
            'requested_package_code' => 'free',
            'status' => $status,
            'current_step' => OnboardingStatusMachine::defaultStepFor($status),
            'admin_status' => $status,
            'payload' => [],
            'verification_status' => [],
            'duplicate_check' => [],
            'mlhub_user_id' => $user->id,
            'mlhub_workspace_id' => $team->id,
            'mlhub_business_id' => $business->id,
        ]);
    }

    return compact('user', 'team', 'integration');
}

function crmHeaders(string $idempotencyKey): array
{
    return [
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) Str::uuid(),
        'Idempotency-Key' => $idempotencyKey,
        'Accept' => 'application/json',
    ];
}

function crmUrl(string $externalBusinessId): string
{
    return '/api/v1/partners/fizahub/businesses/'.$externalBusinessId.'/crm-login-links';
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 100);
    config()->set('modules.apipartnerfizahub.one_time_login_ttl_minutes', 5);
    bootProductionLikeSchema();
});

afterEach(function (): void {
    Auth::guard('web')->logout();
    $this->flushSession();
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

test('CRM link is issued only for ready or completed onboarding', function (string $status, int $expected): void {
    seedCrmBusiness('biz-crm-'.$status, $status);

    $response = $this->postJson(crmUrl('biz-crm-'.$status), [], crmHeaders('crm-'.$status));
    $response->assertStatus($expected);

    if ($expected === 409) {
        $response->assertJsonPath('error.code', 'onboarding_not_ready');
    } else {
        $response->assertJsonStructure(['data' => ['url', 'expires_at', 'expires_in_seconds']]);
    }
})->with([
    [OnboardingStatusMachine::AWAITING_CONSULTANT, 409],
    [OnboardingStatusMachine::CONFIGURING, 409],
    [OnboardingStatusMachine::READY, 201],
    [OnboardingStatusMachine::COMPLETED, 201],
]);

test('legacy integration without onboarding is not allowed to issue a CRM link', function (): void {
    seedCrmBusiness('biz-crm-legacy', null);

    $this->postJson(crmUrl('biz-crm-legacy'), [], crmHeaders('crm-legacy'))
        ->assertConflict()
        ->assertJsonPath('error.code', 'onboarding_not_ready');

    expect(PartnerOneTimeLogin::query()->count())->toBe(0);
});

test('same key returns the same live CRM link bound to the non-admin mapped user and workspace', function (): void {
    $mapping = seedCrmBusiness('biz-crm-replay', OnboardingStatusMachine::READY);
    $headers = crmHeaders('crm-live-1');

    $first = $this->postJson(crmUrl('biz-crm-replay'), [], $headers)->assertCreated();
    $second = $this->postJson(crmUrl('biz-crm-replay'), [], crmHeaders('crm-live-1'))->assertCreated();

    expect($second->json('data.url'))->toBe($first->json('data.url'))
        ->and($first->json('data.expires_in_seconds'))->toBeGreaterThan(0)
        ->and(PartnerOneTimeLogin::query()->count())->toBe(1)
        ->and($mapping['user']->is_super_admin)->toBeFalse();

    $this->withSession(['portal_team_id' => 999999])
        ->get((string) $first->json('data.url'))
        ->assertRedirect(route('portal.dashboard'));

    $this->assertAuthenticatedAs($mapping['user']);
    expect(session('portal_team_id'))->toBe($mapping['team']->id);
});

test('a consumed or expired key is not reusable and a new key creates a different link', function (): void {
    seedCrmBusiness('biz-crm-replace', OnboardingStatusMachine::COMPLETED);
    $url = crmUrl('biz-crm-replace');

    $first = $this->postJson($url, [], crmHeaders('crm-used'))->assertCreated();
    $this->get((string) $first->json('data.url'))->assertRedirect(route('portal.dashboard'));
    Auth::guard('web')->logout();
    $this->flushSession();

    $this->postJson($url, [], crmHeaders('crm-used'))
        ->assertConflict()
        ->assertJsonPath('error.code', 'crm_login_link_not_reusable')
        ->assertJsonPath('error.details.next_action', 'new_idempotency_key');

    $replacement = $this->postJson($url, [], crmHeaders('crm-used-replacement'))->assertCreated();
    expect($replacement->json('data.url'))->not->toBe($first->json('data.url'));

    $expired = PartnerOneTimeLogin::query()->where('idempotency_key', 'crm-used-replacement')->firstOrFail();
    $expired->forceFill(['expires_at' => now()->subSecond()])->save();

    $this->postJson($url, [], crmHeaders('crm-used-replacement'))
        ->assertConflict()
        ->assertJsonPath('error.code', 'crm_login_link_not_reusable');
});
