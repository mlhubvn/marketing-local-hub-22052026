<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingStatusHistory;
use Modules\APIPartnerFizaHUB\Models\PartnerPackageAssignment;
use Modules\AppAffiliate\Models\AffiliateProfile;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

function createOnboardingTestTables(): void
{
    dropFizaHubPartnerTables();
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

    createFizaHubPartnerTables();
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
    dropFizaHubPartnerTables();
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

test('onboarding always provisions a free account awaiting consultant with requested package stored separately', function (): void {
    $requestId = (string) str()->uuid();

    $response = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload(),
        onboardingHeaders(['X-Request-Id' => $requestId])
    );

    $response->assertCreated()
        ->assertJsonPath('data.status', 'awaiting_consultant')
        ->assertJsonPath('data.current_step', 'consultant_contact')
        ->assertJsonPath('data.status_label', 'Chờ tư vấn viên liên hệ')
        ->assertJsonPath('data.package_code', 'free')
        ->assertJsonPath('data.requested_package_code', 'base')
        ->assertJsonPath('data.approved_package_code', null)
        ->assertJsonPath('data.account_created', true)
        ->assertJsonPath('data.business_created', true)
        ->assertJsonPath('data.integration_created', true)
        ->assertJsonPath('data.request_id', $requestId);

    expect(User::query()->count())->toBe(1)
        ->and(Team::query()->count())->toBe(1)
        ->and(LocalBusiness::query()->count())->toBe(1)
        ->and(PartnerIntegration::query()->count())->toBe(1)
        ->and(PartnerOnboardingRequest::query()->count())->toBe(1)
        ->and(AffiliateProfile::query()->count())->toBe(1)
        ->and(SupportTicket::query()->count())->toBe(1)
        ->and(PartnerPackageAssignment::query()->count())->toBe(1);

    $user = User::query()->firstOrFail();
    $business = LocalBusiness::query()->firstOrFail();
    $plan = AdminPlan::query()->where('slug', 'mlhub-free-da-nang')->firstOrFail();
    $integration = PartnerIntegration::query()->firstOrFail();
    $assignment = PartnerPackageAssignment::query()->firstOrFail();

    expect($user->email)->toBe('owner.a@example.com')
        ->and($user->timezone)->toBe('Asia/Ho_Chi_Minh')
        ->and($user->locale)->toBe('vi')
        ->and($user->plan_id)->toBe($plan->id)
        ->and($user->username)->toStartWith('fizahub_')
        ->and($user->email_verified_at)->not->toBeNull()
        ->and(Hash::isHashed((string) $user->getRawOriginal('password')))->toBeTrue()
        ->and(json_encode($response->json()))->not->toContain('password')
        ->and($business->industry_category_code)->toBe('restaurant_eatery')
        ->and($integration->package_code)->toBe('free')
        ->and($integration->metadata['tax_code'] ?? null)->toBe('0101234567')
        ->and($integration->metadata['business_license_number'] ?? null)->toBe('GPKD123')
        ->and($assignment->package_code)->toBe('free')
        ->and($assignment->status)->toBe('active')
        ->and($response->json('data.mlhub_user_id'))->toBe($user->id)
        ->and($response->json('data.mlhub_business_id'))->toBe($business->id);

    expect(PartnerOnboardingStatusHistory::query()->where('to_status', 'awaiting_consultant')->exists())->toBeTrue();
});

test('same request id upsert updates records once and keeps a single onboarding ticket', function (): void {
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
        ->assertJsonPath('data.status', 'awaiting_consultant');

    expect(User::query()->count())->toBe(1)
        ->and(LocalBusiness::query()->count())->toBe(1)
        ->and(PartnerIntegration::query()->count())->toBe(1)
        ->and(PartnerOnboardingRequest::query()->count())->toBe(1)
        ->and(SupportTicket::query()->count())->toBe(1)
        ->and(PartnerPackageAssignment::query()->count())->toBe(1)
        ->and(User::query()->value('name'))->toBe('Nguyen Van B')
        ->and(LocalBusiness::query()->value('name'))->toBe('Quan Com B');
});

test('same external business id through a new request does not duplicate mlhub records', function (): void {
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
        ->assertJsonPath('data.status', 'awaiting_consultant');

    expect(User::query()->count())->toBe(1)
        ->and(LocalBusiness::query()->count())->toBe(1)
        ->and(PartnerIntegration::query()->count())->toBe(1)
        ->and(PartnerOnboardingRequest::query()->count())->toBe(2)
        ->and(LocalBusiness::query()->value('name'))->toBe('Updated Shop');
});

test('duplicate owner email still provisions a Free account with a provisional email and needs_review', function (): void {
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
        ->assertJsonPath('data.current_step', 'needs_review')
        ->assertJsonPath('data.package_code', 'free')
        ->assertJsonPath('data.account_created', true)
        ->assertJsonPath('data.duplicate_check.0.type', 'email')
        ->assertJsonPath('data.duplicate_check.0.id', $existing->id);

    $integration = PartnerIntegration::query()->firstOrFail();
    $provisioned = User::query()->findOrFail($integration->mlhub_user_id);

    expect(PartnerIntegration::query()->count())->toBe(1)
        ->and(User::query()->count())->toBe(2)
        ->and(SupportTicket::query()->count())->toBe(1)
        ->and($provisioned->email)->not->toBe('owner.a@example.com')
        ->and($provisioned->email)->toStartWith('fizahub+')
        ->and($integration->metadata['uses_provisional_email'] ?? null)->toBeTrue();
});

test('duplicate tax code on another integration still provisions with needs_review', function (): void {
    $otherUser = User::query()->create([
        'name' => 'Other',
        'username' => 'otheruser',
        'email' => 'other@example.com',
        'password' => 'password-password-password-password-password-password-1234',
    ]);

    $otherBusiness = LocalBusiness::query()->create([
        'user_id' => $otherUser->id,
        'name' => 'Other Shop',
        'type' => 'other',
    ]);

    PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'other-biz',
        'mlhub_user_id' => $otherUser->id,
        'mlhub_business_id' => $otherBusiness->id,
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
        ->assertJsonPath('data.account_created', true)
        ->assertJsonPath('data.duplicate_check.0.type', 'tax_code');

    expect(User::query()->where('email', 'fresh@example.com')->exists())->toBeTrue()
        ->and(PartnerIntegration::query()->count())->toBe(2);
});

test('unverified identity still provisions an awaiting_consultant account', function (): void {
    $requestId = (string) str()->uuid();

    $response = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload([
            'verification' => [
                'identity_verified' => false,
                'verified_at' => null,
            ],
        ]),
        onboardingHeaders(['X-Request-Id' => $requestId])
    );

    $response->assertCreated()
        ->assertJsonPath('data.status', 'awaiting_consultant')
        ->assertJsonPath('data.current_step', 'consultant_contact')
        ->assertJsonPath('data.request_id', $requestId);

    $user = User::query()->firstOrFail();

    expect(User::query()->count())->toBe(1)
        ->and(SupportTicket::query()->count())->toBe(1)
        ->and($user->email_verified_at)->toBeNull();
});

test('partner can confirm an onboarding request', function (): void {
    $requestId = (string) str()->uuid();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload(),
        onboardingHeaders(['X-Request-Id' => $requestId])
    )->assertCreated();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests/'.$requestId.'/confirm',
        ['note' => 'Owner confirmed by phone'],
        onboardingHeaders()
    )
        ->assertOk()
        ->assertJsonPath('data.status', 'awaiting_consultant');

    expect(PartnerOnboardingRequest::query()->value('partner_confirmed_at'))->not->toBeNull();
});

test('partner can cancel an onboarding request', function (): void {
    $requestId = (string) str()->uuid();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload(),
        onboardingHeaders(['X-Request-Id' => $requestId])
    )->assertCreated();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests/'.$requestId.'/cancel',
        ['reason' => 'Owner changed their mind'],
        onboardingHeaders()
    )
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled')
        ->assertJsonPath('data.current_step', 'cancelled');

    expect(PartnerOnboardingRequest::query()->value('status'))->toBe('cancelled');
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
        ->assertJsonPath('data.status', 'awaiting_consultant');
});

test('onboarding show returns a typed onboarding_request_not_found for an unknown request id', function (): void {
    $this->getJson(
        '/api/v1/partners/fizahub/onboarding-requests/'.((string) str()->uuid()),
        onboardingHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'onboarding_request_not_found')
        ->assertJsonPath('error.details.next_action', 'create_onboarding_request');
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

test('onboarding requires Idempotency-Key header', function (): void {
    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload(),
        onboardingHeaders(['Idempotency-Key' => ''])
    )
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'validation_failed');

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload(),
        onboardingHeaders(['Idempotency-Key' => str_repeat('x', 129)])
    )
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'validation_failed');

    expect(PartnerOnboardingRequest::query()->count())->toBe(0);
});

test('identity_verified true with null verified_at still sets email_verified_at', function (): void {
    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload([
            'owner' => ['email' => 'verified-null@example.com'],
            'external_business_id' => 'fh-biz-verified-null',
            'verification' => [
                'identity_verified' => true,
                'verified_at' => null,
                'verified_by' => 'fizahub',
            ],
        ]),
        onboardingHeaders()
    )->assertCreated();

    $user = User::query()->where('email', 'verified-null@example.com')->firstOrFail();

    expect($user->email_verified_at)->not->toBeNull()
        ->and(Hash::isHashed((string) $user->getRawOriginal('password')))->toBeTrue();
});

test('onboarding show with an empty, literal placeholder, or malformed request_id never 500s', function (): void {
    // A partner client that forgot to substitute a Postman variable will literally send
    // the placeholder text as the path segment. This must resolve as "not found", never crash.
    $this->getJson(
        '/api/v1/partners/fizahub/onboarding-requests/%7B%7Bonboarding_request_id%7D%7D',
        onboardingHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'onboarding_request_not_found');

    $this->getJson(
        '/api/v1/partners/fizahub/onboarding-requests/not-a-valid-uuid',
        onboardingHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'onboarding_request_not_found');
});

test('cancelling an already cancelled onboarding request is idempotent, not a 500 or a state conflict', function (): void {
    $requestId = (string) str()->uuid();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload(['external_business_id' => 'fh-biz-cancel-twice']),
        onboardingHeaders(['X-Request-Id' => $requestId])
    )->assertCreated();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests/'.$requestId.'/cancel',
        ['reason' => 'First cancel'],
        onboardingHeaders()
    )->assertOk()->assertJsonPath('data.status', 'cancelled');

    // Same request_id, cancelled again — must stay a clean 200/cancelled, not 422/500.
    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests/'.$requestId.'/cancel',
        ['reason' => 'Second cancel attempt'],
        onboardingHeaders()
    )->assertOk()->assertJsonPath('data.status', 'cancelled');
});

test('cancel returns a typed 404 for an unknown request_id instead of 500', function (): void {
    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests/'.((string) str()->uuid()).'/cancel',
        ['reason' => 'Does not exist'],
        onboardingHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'onboarding_request_not_found');
});

test('confirm returns a typed 404 for an unknown request_id instead of 500', function (): void {
    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests/'.((string) str()->uuid()).'/confirm',
        ['note' => 'Does not exist'],
        onboardingHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'onboarding_request_not_found');
});
