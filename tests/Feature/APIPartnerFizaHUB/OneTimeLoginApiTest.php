<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerApiLog;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerOneTimeLogin;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

function createOneTimeLoginTables(): void
{
    dropFizaHubPartnerTables();
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');
    Schema::dropIfExists('audit_logs');

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
        $table->string('remember_token', 100)->nullable();
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

    Schema::create('support_tickets', function (Blueprint $table): void {
        $table->id();
        $table->string('id_secure', 40)->unique();
        $table->unsignedBigInteger('uid');
        $table->unsignedBigInteger('open_by');
        $table->unsignedBigInteger('team_id')->nullable();
        $table->unsignedBigInteger('cate_id')->nullable();
        $table->unsignedBigInteger('type_id')->nullable();
        $table->string('title', 255);
        $table->text('content');
        $table->unsignedTinyInteger('status')->default(1);
        $table->boolean('pin')->default(false);
        $table->boolean('user_read')->default(false);
        $table->boolean('admin_read')->default(true);
        $table->unsignedInteger('changed')->nullable();
        $table->unsignedInteger('created')->nullable();
    });

    Schema::create('audit_logs', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('causer_user_id')->nullable();
        $table->string('event');
        $table->string('description')->nullable();
        $table->string('subject_type')->nullable();
        $table->unsignedBigInteger('subject_id')->nullable();
        $table->string('route_name')->nullable();
        $table->string('area')->default('admin');
        $table->string('ip_address')->nullable();
        $table->text('user_agent')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamps();
    });

    createFizaHubPartnerTables();
}

/**
 * @return array{user: User, team: Team, business: LocalBusiness, integration: PartnerIntegration}
 */
function seedOneTimeLoginBusiness(string $externalBusinessId, string $email): array
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
        'name' => 'Owner '.$externalBusinessId,
        'username' => 'user_'.Str::lower(Str::random(8)),
        'email' => $email,
        'password' => 'password-password-password-password-password-password-1234',
        'plan_id' => $plan->id,
        'locale' => 'vi',
        'timezone' => 'Asia/Ho_Chi_Minh',
        'is_super_admin' => false,
    ]);

    $team = Team::query()->create([
        'name' => $user->name.' Team',
        'slug' => 'team-'.$user->id,
        'owner_user_id' => $user->id,
    ]);

    $business = LocalBusiness::query()->create([
        'user_id' => $user->id,
        'name' => 'Shop '.$externalBusinessId,
        'type' => 'Restaurant',
    ]);

    $integration = PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => $externalBusinessId,
        'external_user_id' => 'ext-'.$externalBusinessId,
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'package_code' => 'base',
        'status' => 'active',
    ]);

    return compact('user', 'team', 'business', 'integration');
}

function oneTimeLoginHeaders(array $overrides = []): array
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
    config()->set('modules.apipartnerfizahub.one_time_login_ttl_minutes', 5);
    createOneTimeLoginTables();
});

afterEach(function (): void {
    dropFizaHubPartnerTables();
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');
    Schema::dropIfExists('audit_logs');
});

test('issues a single-use one-time login with hashed token and five minute ttl', function (): void {
    $seed = seedOneTimeLoginBusiness('biz-login', 'login@example.com');
    $idempotencyKey = (string) str()->uuid();
    $requestId = (string) str()->uuid();

    $response = $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-login/one-time-login',
        [],
        oneTimeLoginHeaders([
            'X-Request-Id' => $requestId,
            'Idempotency-Key' => $idempotencyKey,
        ])
    )->assertCreated();

    $url = $response->json('data.url');
    $expiresAt = $response->json('data.expires_at');

    expect($url)->toBeString()->toContain('/partners/fizahub/one-time-login/');
    expect($expiresAt)->toBeString()->not->toBeEmpty();

    $row = PartnerOneTimeLogin::query()->sole();
    expect($row->token_hash)->toHaveLength(64)
        ->and($row->user_id)->toBe($seed['user']->id)
        ->and($row->used_at)->toBeNull()
        ->and(now()->diffInMinutes($row->expires_at))->toBeGreaterThanOrEqual(4)
        ->and(now()->diffInMinutes($row->expires_at))->toBeLessThanOrEqual(5);

    $plainToken = basename(parse_url($url, PHP_URL_PATH));
    expect(strlen($plainToken))->toBe(64)
        ->and($row->token_hash)->toBe(hash('sha256', $plainToken))
        ->and(DB::table('partner_one_time_logins')->where('token_hash', $plainToken)->exists())->toBeFalse();

    $audit = DB::table('audit_logs')->where('event', 'partner.fizahub.login.issue')->first();
    expect($audit)->not->toBeNull();
    $metadata = json_decode((string) $audit->metadata, true);
    expect($metadata['url'] ?? null)->toBe('[REDACTED]')
        ->and(json_encode($metadata))->not->toContain($plainToken)
        ->and(json_encode($metadata))->not->toContain('/partners/fizahub/one-time-login/');

    $log = PartnerApiLog::query()->where('idempotency_key', $idempotencyKey)->firstOrFail();
    $payload = $log->response_payload;
    expect(data_get($payload, 'data.url'))->toBe('[REDACTED]');
});

test('refuses one-time login while onboarding is still awaiting a consultant', function (): void {
    $seed = seedOneTimeLoginBusiness('biz-not-ready', 'notready@example.com');

    PartnerOnboardingRequest::query()->create([
        'partner_code' => 'fizahub',
        'request_id' => (string) str()->uuid(),
        'external_business_id' => 'biz-not-ready',
        'package_code' => 'free',
        'status' => OnboardingStatusMachine::AWAITING_CONSULTANT,
        'current_step' => OnboardingStatusMachine::defaultStepFor(OnboardingStatusMachine::AWAITING_CONSULTANT),
        'mlhub_user_id' => $seed['user']->id,
        'mlhub_business_id' => $seed['business']->id,
    ]);

    $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-not-ready/one-time-login',
        [],
        oneTimeLoginHeaders(['Idempotency-Key' => (string) str()->uuid()])
    )
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'onboarding_not_ready')
        ->assertJsonPath('error.message', 'Tài khoản đang chờ tư vấn viên MLHUB hoàn tất cấu hình.');

    expect(PartnerOneTimeLogin::query()->count())->toBe(0);
});

test('allows one-time login once onboarding is marked ready', function (): void {
    $seed = seedOneTimeLoginBusiness('biz-ready', 'ready@example.com');

    PartnerOnboardingRequest::query()->create([
        'partner_code' => 'fizahub',
        'request_id' => (string) str()->uuid(),
        'external_business_id' => 'biz-ready',
        'package_code' => 'free',
        'status' => OnboardingStatusMachine::READY,
        'current_step' => OnboardingStatusMachine::defaultStepFor(OnboardingStatusMachine::READY),
        'mlhub_user_id' => $seed['user']->id,
        'mlhub_business_id' => $seed['business']->id,
    ]);

    $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-ready/one-time-login',
        [],
        oneTimeLoginHeaders(['Idempotency-Key' => (string) str()->uuid()])
    )->assertCreated();

    expect(PartnerOneTimeLogin::query()->count())->toBe(1);
});

test('refuses one-time login for missing or incomplete integrations', function (): void {
    $this->postJson(
        '/api/v1/partners/fizahub/businesses/missing-biz/one-time-login',
        [],
        oneTimeLoginHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'resource_not_found');

    $plan = AdminPlan::query()->create([
        'name' => 'Plan',
        'slug' => 'plan-incomplete',
        'status' => true,
        'currency' => 'VND',
        'price' => 0,
        'permissions' => [],
    ]);
    $user = User::query()->create([
        'name' => 'Incomplete',
        'username' => 'incomplete_'.Str::lower(Str::random(6)),
        'email' => 'incomplete@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'plan_id' => $plan->id,
    ]);
    $team = Team::query()->create([
        'name' => 'Incomplete Team',
        'slug' => 'team-incomplete',
        'owner_user_id' => $user->id,
    ]);

    PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'incomplete-biz',
        'external_user_id' => 'ext-incomplete',
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => null,
        'package_code' => 'base',
        'status' => 'active',
    ]);

    $this->postJson(
        '/api/v1/partners/fizahub/businesses/incomplete-biz/one-time-login',
        [],
        oneTimeLoginHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'integration_not_found');
});

test('consume logs in mapped user once then rejects reuse expired invalid and unknown tokens', function (): void {
    $seed = seedOneTimeLoginBusiness('biz-consume', 'consume@example.com');

    $issue = $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-consume/one-time-login',
        [],
        oneTimeLoginHeaders()
    )->assertCreated();

    $url = $issue->json('data.url');
    $plainToken = basename(parse_url($url, PHP_URL_PATH));

    $this->withSession(['_token' => 'before-login'])
        ->get($url)
        ->assertRedirect(route('portal.dashboard'));

    $this->assertAuthenticatedAs($seed['user']);
    expect(Auth::guard('web')->user()->is_super_admin)->toBeFalse();
    expect(PartnerOneTimeLogin::query()->where('token_hash', hash('sha256', $plainToken))->value('used_at'))
        ->not->toBeNull();
    expect(session()->token())->not->toBe('before-login');

    Auth::guard('web')->logout();
    $this->flushSession();
    $this->assertGuest();

    $this->get($url)->assertForbidden();
    $this->assertGuest();

    $expiredToken = bin2hex(random_bytes(32));
    PartnerOneTimeLogin::query()->create([
        'partner_integration_id' => $seed['integration']->id,
        'user_id' => $seed['user']->id,
        'request_id' => (string) str()->uuid(),
        'token_hash' => hash('sha256', $expiredToken),
        'expires_at' => now()->subMinute(),
        'used_at' => null,
    ]);
    $expiredUrl = URL::temporarySignedRoute(
        'partner.fizahub.login.consume',
        now()->addMinutes(5),
        ['token' => $expiredToken]
    );
    $this->get($expiredUrl)->assertForbidden();
    $this->assertGuest();

    $tampered = $url.(str_contains($url, '?') ? '&' : '?').'signature=deadbeef';
    $this->get($tampered)->assertForbidden();
    $this->assertGuest();

    $unknownToken = bin2hex(random_bytes(32));
    $unknownUrl = URL::temporarySignedRoute(
        'partner.fizahub.login.consume',
        now()->addMinutes(5),
        ['token' => $unknownToken]
    );
    $this->get($unknownUrl)->assertForbidden();
    $this->assertGuest();
});

test('consume marks used_at under lock so a second consumer cannot succeed', function (): void {
    $seed = seedOneTimeLoginBusiness('biz-lock', 'lock@example.com');
    $plainToken = bin2hex(random_bytes(32));

    $login = PartnerOneTimeLogin::query()->create([
        'partner_integration_id' => $seed['integration']->id,
        'user_id' => $seed['user']->id,
        'request_id' => (string) str()->uuid(),
        'token_hash' => hash('sha256', $plainToken),
        'expires_at' => now()->addMinutes(5),
        'used_at' => null,
    ]);

    $url = URL::temporarySignedRoute(
        'partner.fizahub.login.consume',
        now()->addMinutes(5),
        ['token' => $plainToken]
    );

    $this->get($url)->assertRedirect(route('portal.dashboard'));
    $this->assertAuthenticatedAs($seed['user']);

    $login->refresh();
    expect($login->used_at)->not->toBeNull();

    Auth::guard('web')->logout();
    $this->flushSession();

    $this->get($url)->assertForbidden();
    $this->assertGuest();
    expect(PartnerOneTimeLogin::query()->whereKey($login->id)->whereNotNull('used_at')->exists())->toBeTrue();
});
