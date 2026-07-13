<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\AppAffiliate\Models\AffiliateProfile;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

function createOnboardingTestTables(): void
{
    Schema::dropIfExists('partner_one_time_logins');
    Schema::dropIfExists('partner_api_logs');
    Schema::dropIfExists('partner_onboarding_requests');
    Schema::dropIfExists('partner_integrations');
    Schema::dropIfExists('affiliate_profiles');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');

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
        $table->unsignedBigInteger('next_plan_id')->nullable();
        $table->timestamp('plan_started_at')->nullable();
        $table->timestamp('plan_expires_at')->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('referral_code', 20)->nullable();
        $table->unsignedBigInteger('referred_by_user_id')->nullable();
        $table->boolean('is_super_admin')->default(false);
        $table->timestamps();
    });

    Schema::create('teams', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('slug');
        $table->text('description')->nullable();
        $table->unsignedBigInteger('owner_user_id')->nullable();
        $table->json('enabled_modules')->nullable();
        $table->timestamps();
    });

    Schema::create('team_user', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('team_id');
        $table->unsignedBigInteger('user_id');
        $table->string('role', 50)->default('member');
        $table->json('permissions')->nullable();
        $table->json('managed_account_ids')->nullable();
        $table->timestamps();
        $table->unique(['team_id', 'user_id']);
    });

    Schema::create('lb_businesses', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('name');
        $table->string('type', 60)->default('other');
        $table->string('industry_group_code', 64)->nullable();
        $table->string('industry_category_code', 80)->nullable();
        $table->json('industry_metadata')->nullable();
        $table->string('taxonomy_version', 20)->nullable();
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->string('website')->nullable();
        $table->text('address')->nullable();
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

    Schema::create('affiliate_profiles', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id')->unique();
        $table->unsignedInteger('clicks')->default(0);
        $table->unsignedInteger('conversions')->default(0);
        $table->decimal('total_approved', 12, 2)->default(0);
        $table->decimal('total_withdrawal', 12, 2)->default(0);
        $table->decimal('total_balance', 12, 2)->default(0);
        $table->timestamps();
    });

    $migration = require base_path('modules/APIPartnerFizaHUB/Database/Migrations/2026_07_13_000000_create_fizahub_partner_api_tables.php');
    $migration->up();
}

function seedOnboardingPlan(): AdminPlan
{
    return AdminPlan::query()->create([
        'name' => 'MLHUB Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'free_plan' => true,
        'default_signup_plan' => true,
        'currency' => 'VND',
        'price' => 0,
        'permissions' => [],
    ]);
}

function onboardingHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Idempotency-Key' => (string) str()->uuid(),
        'Accept' => 'application/json',
    ], $overrides);
}

function validOnboardingPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'external_business_id' => 'fh-biz-001',
        'external_user_id' => 'fh-user-001',
        'package_code' => 'base',
        'owner' => [
            'name' => 'Nguyen Van A',
            'phone' => '0901 234 567',
            'email' => 'Owner.A@Example.com',
        ],
        'business' => [
            'name' => 'Quan Com A',
            'industry' => 'restaurant_food',
            'phone' => '0901-234-567',
            'email' => 'shop@example.com',
            'website' => 'https://shop.example.com',
            'address' => 'Da Nang',
            'tax_code' => '0101-234-567',
            'business_license_number' => 'GPKD 123',
        ],
        'verification' => [
            'identity_verified' => true,
            'verified_at' => '2026-07-13T10:00:00+07:00',
            'verified_by' => 'fizahub',
        ],
    ], $overrides);
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);
    createOnboardingTestTables();
    seedOnboardingPlan();
});

afterEach(function (): void {
    Schema::dropIfExists('partner_one_time_logins');
    Schema::dropIfExists('partner_api_logs');
    Schema::dropIfExists('partner_onboarding_requests');
    Schema::dropIfExists('partner_integrations');
    Schema::dropIfExists('affiliate_profiles');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');
});

test('onboarding validation requires core fields and supported package', function (): void {
    $this->postJson('/api/v1/partners/fizahub/onboarding-requests', [
        'package_code' => 'unknown',
    ], onboardingHeaders())
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'validation_failed');
});

test('onboarding rejects prohibited identity document keys', function (): void {
    $payload = validOnboardingPayload([
        'owner' => [
            'cccd' => '012345678901',
            'identity_document' => ['image' => 'x'],
        ],
    ]);

    $this->postJson('/api/v1/partners/fizahub/onboarding-requests', $payload, onboardingHeaders())
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'validation_failed');
});

test('unverified onboarding becomes pending_verification with support ticket', function (): void {
    $requestId = (string) str()->uuid();
    $payload = validOnboardingPayload([
        'verification' => [
            'identity_verified' => false,
            'verified_at' => null,
        ],
    ]);

    $response = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders(['X-Request-Id' => $requestId])
    );

    $response->assertStatus(202)
        ->assertJsonPath('data.status', 'pending_verification')
        ->assertJsonPath('data.current_step', 'verification')
        ->assertJsonPath('data.request_id', $requestId);

    expect($response->json('data.support_ticket_id'))->toBeString()->not->toBeEmpty();
    expect(User::query()->count())->toBe(0);
    expect(SupportTicket::query()->count())->toBe(1);
});

test('duplicate email without mapping becomes needs_review and does not attach', function (): void {
    $existing = User::query()->create([
        'name' => 'Existing',
        'username' => 'existing1',
        'email' => 'owner.a@example.com',
        'password' => 'password-password-password-password-password-password-1234',
    ]);

    $response = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload(),
        onboardingHeaders()
    );

    $response->assertStatus(202)
        ->assertJsonPath('data.status', 'needs_review')
        ->assertJsonPath('data.current_step', 'duplicate_review')
        ->assertJsonPath('data.duplicate_check.0.type', 'email')
        ->assertJsonPath('data.duplicate_check.0.id', $existing->id);

    expect(PartnerIntegration::query()->count())->toBe(0);
    expect(User::query()->count())->toBe(1);
    expect(SupportTicket::query()->count())->toBe(1);
});

test('provision creates user team business integration and maps restaurant_food', function (): void {
    $requestId = (string) str()->uuid();

    $response = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload(),
        onboardingHeaders(['X-Request-Id' => $requestId])
    );

    $response->assertCreated()
        ->assertJsonPath('data.status', 'completed')
        ->assertJsonPath('data.current_step', 'ready')
        ->assertJsonPath('data.package_code', 'base')
        ->assertJsonPath('data.request_id', $requestId);

    expect(User::query()->count())->toBe(1)
        ->and(Team::query()->count())->toBe(1)
        ->and(LocalBusiness::query()->count())->toBe(1)
        ->and(PartnerIntegration::query()->count())->toBe(1)
        ->and(PartnerOnboardingRequest::query()->count())->toBe(1)
        ->and(AffiliateProfile::query()->count())->toBe(1);

    $user = User::query()->firstOrFail();
    $business = LocalBusiness::query()->firstOrFail();
    $plan = AdminPlan::query()->where('slug', 'mlhub-free-da-nang')->firstOrFail();
    $integration = PartnerIntegration::query()->firstOrFail();

    expect($user->email)->toBe('owner.a@example.com')
        ->and($user->timezone)->toBe('Asia/Ho_Chi_Minh')
        ->and($user->locale)->toBe('vi')
        ->and($user->plan_id)->toBe($plan->id)
        ->and($user->username)->toStartWith('fizahub_')
        ->and(strlen((string) preg_replace('/^fizahub_/', '', (string) $user->username)))->toBe(12)
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($response->json())->not->toHaveKey('data.password')
        ->and(json_encode($response->json()))->not->toContain('password')
        ->and($business->industry_category_code)->toBe('restaurant_eatery')
        ->and($integration->metadata['tax_code'] ?? null)->toBe('0101234567')
        ->and($integration->metadata['business_license_number'] ?? null)->toBe('GPKD123')
        ->and($response->json('data.mlhub_user_id'))->toBe($user->id)
        ->and($response->json('data.mlhub_business_id'))->toBe($business->id);
});

test('same request id upsert updates without duplicating provisioned records', function (): void {
    $requestId = (string) str()->uuid();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload(),
        onboardingHeaders(['X-Request-Id' => $requestId])
    )->assertCreated();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload([
            'owner' => ['name' => 'Nguyen Van B'],
            'business' => ['name' => 'Quan Com B'],
        ]),
        onboardingHeaders(['X-Request-Id' => $requestId])
    )
        ->assertOk()
        ->assertJsonPath('data.status', 'completed');

    expect(User::query()->count())->toBe(1)
        ->and(LocalBusiness::query()->count())->toBe(1)
        ->and(PartnerIntegration::query()->count())->toBe(1)
        ->and(User::query()->value('name'))->toBe('Nguyen Van B')
        ->and(LocalBusiness::query()->value('name'))->toBe('Quan Com B');
});

test('same external business id through new request does not duplicate mlhub records', function (): void {
    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload(),
        onboardingHeaders(['X-Request-Id' => (string) str()->uuid()])
    )->assertCreated();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload([
            'business' => ['name' => 'Updated Shop'],
        ]),
        onboardingHeaders(['X-Request-Id' => (string) str()->uuid()])
    )
        ->assertCreated()
        ->assertJsonPath('data.status', 'completed');

    expect(User::query()->count())->toBe(1)
        ->and(LocalBusiness::query()->count())->toBe(1)
        ->and(PartnerIntegration::query()->count())->toBe(1)
        ->and(PartnerOnboardingRequest::query()->count())->toBe(2)
        ->and(LocalBusiness::query()->value('name'))->toBe('Updated Shop');
});

test('onboarding show returns status by request id', function (): void {
    $requestId = (string) str()->uuid();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload(),
        onboardingHeaders(['X-Request-Id' => $requestId])
    )->assertCreated();

    $this->getJson(
        '/api/v1/partners/fizahub/onboarding-requests/'.$requestId,
        onboardingHeaders()
    )
        ->assertOk()
        ->assertJsonPath('data.request_id', $requestId)
        ->assertJsonPath('data.status', 'completed');
});

test('business creation failure rolls back user team and integration', function (): void {
    LocalBusiness::creating(function (): void {
        throw new RuntimeException('forced business failure');
    });

    try {
        $this->postJson(
            '/api/v1/partners/fizahub/onboarding-requests',
            validOnboardingPayload([
                'external_business_id' => 'fh-biz-rollback',
                'owner' => ['email' => 'rollback@example.com'],
            ]),
            onboardingHeaders()
        )
            ->assertStatus(500)
            ->assertJsonPath('error.code', 'partner_api_error');

        expect(User::query()->where('email', 'rollback@example.com')->exists())->toBeFalse()
            ->and(Team::query()->count())->toBe(0)
            ->and(PartnerIntegration::query()->count())->toBe(0)
            ->and(LocalBusiness::query()->count())->toBe(0)
            ->and(PartnerOnboardingRequest::query()->count())->toBe(0);
    } finally {
        LocalBusiness::flushEventListeners();
    }
});

test('duplicate tax code on another integration becomes needs_review', function (): void {
    $otherUser = User::query()->create([
        'name' => 'Other',
        'username' => 'otheruser',
        'email' => 'other@example.com',
        'password' => 'password-password-password-password-password-password-1234',
    ]);

    PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'other-biz',
        'mlhub_user_id' => $otherUser->id,
        'status' => 'active',
        'metadata' => ['tax_code' => '0101234567'],
    ]);

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload([
            'external_business_id' => 'new-biz',
            'owner' => ['email' => 'fresh@example.com'],
        ]),
        onboardingHeaders()
    )
        ->assertStatus(202)
        ->assertJsonPath('data.status', 'needs_review')
        ->assertJsonPath('data.duplicate_check.0.type', 'tax_code');

    expect(User::query()->where('email', 'fresh@example.com')->exists())->toBeFalse();
});
