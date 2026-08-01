<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerPackageAssignment;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportTicketContext;
use Modules\APIPartnerFizaHUB\Services\PartnerIdentityService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

function onboardingUiHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Idempotency-Key' => (string) str()->uuid(),
        'Accept' => 'application/json',
    ], $overrides);
}

function approvedOnboardingPayload(array $overrides = []): array
{
    return array_replace_recursive([
        'external_business_id' => 'fiza-business-001',
        'external_user_id' => 'fiza-user-001',
        'marketing_goal_codes' => ['qr_checkin', 'customer_retention'],
        'requested_package_code' => 'base',
        'owner' => [
            'name' => 'Đoàn Văn Khoa',
            'email' => 'van-khoa.lqd123@gmail.com',
        ],
        'business' => [
            'name' => 'Fiza Store',
            'industry' => 'restaurant_food',
            'phone' => '0901 234 888',
            'email' => 'contact@fizastore.vn',
            'website' => 'https://fizastore.vn',
            'address' => '888 Lê Duẩn, Đà Nẵng',
        ],
    ], $overrides);
}

function onboardingResourceCounts(): array
{
    return [
        'users' => User::query()->count(),
        'teams' => Team::query()->count(),
        'businesses' => LocalBusiness::query()->count(),
        'integrations' => PartnerIntegration::query()->count(),
        'active_assignments' => PartnerPackageAssignment::query()->where('status', 'active')->count(),
        'onboarding_requests' => PartnerOnboardingRequest::query()->count(),
        'support_tickets' => SupportTicket::query()->count(),
        'onboarding_contexts' => PartnerSupportTicketContext::query()
            ->where('request_code', 'fizahub_onboarding')
            ->count(),
    ];
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);

    bootProductionLikeSchema();

    AdminPlan::query()->create([
        'name' => 'MKT Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'free_plan' => true,
        'default_signup_plan' => true,
        'currency' => 'VND',
        'price' => 0,
        'permissions' => [],
    ]);
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

it('derives lowercase alphanumeric usernames from login email', function (string $email, string $expected): void {
    expect(app(PartnerIdentityService::class)->usernameFromEmail($email))->toBe($expected);
})->with([
    ['van-khoa.lqd123@gmail.com', 'vankhoalqd123'],
    ['doan.van.khoa@gmail.com', 'doanvankhoa'],
    ['đoàn-văn-khoa@example.com', 'doanvankhoa'],
]);

it('uses a deterministic hash fallback when the email local part has no alphanumeric characters', function (): void {
    $email = '---@example.com';
    $username = app(PartnerIdentityService::class)->usernameFromEmail($email);

    expect($username)->toBe('user'.substr(hash('sha256', $email), 0, 8))
        ->and($username)->toMatch('/^[a-z0-9]+$/');
});

it('uses a deterministic suffix when another email owns the base username', function (): void {
    User::query()->create([
        'name' => 'Existing',
        'username' => 'doanvankhoa',
        'email' => 'other@example.com',
        'password' => 'not-used-by-this-test',
    ]);

    $identity = app(PartnerIdentityService::class);
    $username = $identity->availableUsernameFromEmail('doan.van.khoa@gmail.com');

    expect($username)->toMatch('/^doanvankhoa[a-f0-9]{10}$/')
        ->and($username)->toBe($identity->availableUsernameFromEmail('doan.van.khoa@gmail.com'))
        ->and(strlen($username))->toBeLessThanOrEqual(255);
});

test('canonical onboarding payload provisions exactly one complete resource graph without legacy optional fields', function (): void {
    $response = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        approvedOnboardingPayload(),
        onboardingUiHeaders()
    );

    $response->assertCreated()
        ->assertJsonPath('data.external_business_id', 'fiza-business-001')
        ->assertJsonPath('data.external_user_id', 'fiza-user-001')
        ->assertJsonPath('data.username', 'vankhoalqd123')
        ->assertJsonPath('data.already_registered', false)
        ->assertJsonPath('data.account_created', true)
        ->assertJsonPath('data.business_created', true)
        ->assertJsonPath('data.integration_created', true)
        ->assertJsonPath('data.status', 'awaiting_consultant')
        ->assertJsonPath('data.current_step', 'awaiting_consultant')
        ->assertJsonPath('data.requested_package_code', 'base')
        ->assertJsonPath('data.package_code', 'free');

    expect(onboardingResourceCounts())->toBe([
        'users' => 1,
        'teams' => 1,
        'businesses' => 1,
        'integrations' => 1,
        'active_assignments' => 1,
        'onboarding_requests' => 1,
        'support_tickets' => 1,
        'onboarding_contexts' => 1,
    ]);

    $business = LocalBusiness::query()->firstOrFail();
    $ticket = SupportTicket::query()->firstOrFail();
    $context = PartnerSupportTicketContext::query()->firstOrFail();

    expect($business->name)->toBe('Fiza Store')
        ->and($business->industry_category_code)->toBe('restaurant_eatery')
        ->and($business->phone)->toBe('0901234888')
        ->and($business->email)->toBe('contact@fizastore.vn')
        ->and($business->website)->toBe('https://fizastore.vn')
        ->and($business->address)->toBe('888 Lê Duẩn, Đà Nẵng')
        ->and($ticket->uid)->toBe(User::query()->value('id'))
        ->and($ticket->open_by)->toBe(User::query()->value('id'))
        ->and($context->context['ticket_type'] ?? null)->toBe('onboarding')
        ->and($context->context['source'] ?? null)->toBe('fizahub')
        ->and($context->context['preset_code'] ?? null)->toBe('fizahub_onboarding');
});

test('second onboarding with the same mapped email reuses the original request and onboarding ticket', function (): void {
    $first = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        approvedOnboardingPayload(),
        onboardingUiHeaders()
    )->assertCreated();

    $counts = onboardingResourceCounts();
    $loginEmail = User::query()->value('email');

    $second = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        approvedOnboardingPayload([
            'owner' => ['name' => 'Đoàn Văn Khoa Updated'],
            'business' => [
                'name' => 'Fiza Store Updated',
                'phone' => '0909 111 222',
                'email' => 'updated@fizastore.vn',
                'website' => 'https://updated.fizastore.vn',
                'address' => '999 Lê Duẩn, Đà Nẵng',
            ],
        ]),
        onboardingUiHeaders()
    );

    $second->assertOk()
        ->assertJsonPath('data.request_id', $first->json('data.request_id'))
        ->assertJsonPath('data.support_ticket_id', $first->json('data.support_ticket_id'))
        ->assertJsonPath('data.already_registered', true)
        ->assertJsonPath('data.account_created', false)
        ->assertJsonPath('data.business_created', false)
        ->assertJsonPath('data.integration_created', false);

    expect(onboardingResourceCounts())->toBe($counts)
        ->and(User::query()->value('email'))->toBe($loginEmail)
        ->and(User::query()->value('name'))->toBe('Đoàn Văn Khoa Updated')
        ->and(LocalBusiness::query()->value('name'))->toBe('Fiza Store Updated')
        ->and(LocalBusiness::query()->value('phone'))->toBe('0909111222')
        ->and(LocalBusiness::query()->value('email'))->toBe('updated@fizastore.vn');
});

test('email owned by another MKT account returns a typed conflict without provisional resources', function (): void {
    User::query()->create([
        'name' => 'Existing account',
        'username' => 'existingaccount',
        'email' => 'van-khoa.lqd123@gmail.com',
        'password' => 'not-used-by-this-test',
    ]);
    $before = onboardingResourceCounts();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        approvedOnboardingPayload(),
        onboardingUiHeaders()
    )->assertConflict()
        ->assertJsonPath('error.code', 'email_already_registered')
        ->assertJsonPath('error.message', 'Địa chỉ email này đã được đăng ký trên MKT.')
        ->assertJsonPath('error.details.next_action', 'use_existing_account_or_contact_support');

    expect(onboardingResourceCounts())->toBe($before)
        ->and(User::query()->where('email', 'like', '%provisional%')->exists())->toBeFalse();
});

test('mapped business with a different login email returns onboarding email mismatch', function (): void {
    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        approvedOnboardingPayload(),
        onboardingUiHeaders()
    )->assertCreated();
    $before = onboardingResourceCounts();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        approvedOnboardingPayload(['owner' => ['email' => 'different@example.com']]),
        onboardingUiHeaders()
    )->assertConflict()
        ->assertJsonPath('error.code', 'onboarding_email_mismatch')
        ->assertJsonPath('error.message', 'Email đăng ký không khớp với tài khoản đã liên kết trước đó.');

    expect(onboardingResourceCounts())->toBe($before)
        ->and(User::query()->value('email'))->toBe('van-khoa.lqd123@gmail.com');
});

test('needs review returns 202 and blocks the intake step without adding a sixth timeline step', function (): void {
    $otherUser = User::query()->create([
        'name' => 'Other owner',
        'username' => 'otherowner',
        'email' => 'other@example.com',
        'password' => 'not-used-by-this-test',
    ]);
    $otherBusiness = LocalBusiness::query()->create([
        'user_id' => $otherUser->id,
        'name' => 'Other Store',
        'type' => 'other',
    ]);
    PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'other-business',
        'mlhub_user_id' => $otherUser->id,
        'mlhub_business_id' => $otherBusiness->id,
        'package_code' => 'free',
        'status' => 'active',
        'metadata' => ['tax_code' => '0400123456'],
    ]);

    $response = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        approvedOnboardingPayload(['business' => ['tax_code' => '0400 123 456']]),
        onboardingUiHeaders()
    );

    $response->assertStatus(202)
        ->assertJsonPath('data.status', 'needs_review')
        ->assertJsonPath('data.current_step', 'awaiting_consultant')
        ->assertJsonCount(5, 'data.timeline')
        ->assertJsonPath('data.timeline.1.code', 'awaiting_consultant')
        ->assertJsonPath('data.timeline.1.status', 'blocked');
});

test('onboarding detail exposes the stable five-step public timeline', function (): void {
    $created = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        approvedOnboardingPayload(),
        onboardingUiHeaders()
    )->assertCreated();

    $this->getJson(
        '/api/v1/partners/fizahub/onboarding-requests/'.$created->json('data.request_id'),
        onboardingUiHeaders()
    )->assertOk()
        ->assertJsonCount(5, 'data.timeline')
        ->assertJsonPath('data.timeline.0.code', 'account_created')
        ->assertJsonPath('data.timeline.0.status', 'completed')
        ->assertJsonPath('data.timeline.1.code', 'awaiting_consultant')
        ->assertJsonPath('data.timeline.1.status', 'current')
        ->assertJsonPath('data.timeline.2.code', 'in_consultation')
        ->assertJsonPath('data.timeline.2.status', 'pending')
        ->assertJsonPath('data.timeline.3.code', 'configuring')
        ->assertJsonPath('data.timeline.4.code', 'ready');
});

test('a genuinely deleted onboarding ticket is recreated once with an audit reason', function (): void {
    $first = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        approvedOnboardingPayload(),
        onboardingUiHeaders()
    )->assertCreated();
    $firstTicketId = $first->json('data.support_ticket_id');
    $stableCounts = onboardingResourceCounts();

    SupportTicket::query()->firstOrFail()->delete();

    $second = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        approvedOnboardingPayload(),
        onboardingUiHeaders()
    )->assertOk();

    expect($second->json('data.support_ticket_id'))->not->toBe($firstTicketId)
        ->and(onboardingResourceCounts())->toBe(array_merge($stableCounts, [
            'support_tickets' => 1,
            'onboarding_contexts' => 1,
        ]))
        ->and(PartnerSupportTicketContext::query()->firstOrFail()->context['audit_reason'] ?? null)
        ->toBe('referenced_onboarding_ticket_missing');
});

test('missing default plan returns 503 and leaves no partial onboarding graph', function (): void {
    AdminPlan::query()->delete();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        approvedOnboardingPayload(),
        onboardingUiHeaders()
    )->assertServiceUnavailable();

    expect(onboardingResourceCounts())->toBe([
        'users' => 0,
        'teams' => 0,
        'businesses' => 0,
        'integrations' => 0,
        'active_assignments' => 0,
        'onboarding_requests' => 0,
        'support_tickets' => 0,
        'onboarding_contexts' => 0,
    ]);
});

test('a named onboarding unique-key race retries once and leaves exactly one resource graph', function (): void {
    $attempts = 0;

    User::creating(function () use (&$attempts): void {
        $attempts++;

        if ($attempts === 1) {
            throw new QueryException(
                'sqlite',
                'insert into "users" ("username") values (?)',
                ['vankhoalqd123'],
                new PDOException('UNIQUE constraint failed: users.username')
            );
        }
    });

    try {
        $response = $this->postJson(
            '/api/v1/partners/fizahub/onboarding-requests',
            approvedOnboardingPayload(),
            onboardingUiHeaders()
        );

        $response->assertCreated()
            ->assertJsonPath('data.already_registered', false)
            ->assertJsonPath('data.username', 'vankhoalqd123');

        expect($attempts)->toBe(2)
            ->and(onboardingResourceCounts())->toBe([
                'users' => 1,
                'teams' => 1,
                'businesses' => 1,
                'integrations' => 1,
                'active_assignments' => 1,
                'onboarding_requests' => 1,
                'support_tickets' => 1,
                'onboarding_contexts' => 1,
            ]);
    } finally {
        User::flushEventListeners();
    }
});
