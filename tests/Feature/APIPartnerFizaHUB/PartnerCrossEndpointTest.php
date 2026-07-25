<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminSupport\Models\SupportComment;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerApiLog;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

require_once __DIR__.'/FizaHubTestHelpers.php';

function markCrossOnboardingReady(string $externalBusinessId): void
{
    $onboarding = PartnerOnboardingRequest::query()
        ->where('external_business_id', $externalBusinessId)
        ->firstOrFail();

    $onboarding->forceFill([
        'status' => OnboardingStatusMachine::READY,
        'current_step' => OnboardingStatusMachine::defaultStepFor(OnboardingStatusMachine::READY),
        'admin_status' => OnboardingStatusMachine::READY,
    ])->save();
}

function createCrossEndpointTables(): void
{
    Schema::dropIfExists('partner_one_time_logins');
    Schema::dropIfExists('partner_api_logs');
    Schema::dropIfExists('partner_onboarding_requests');
    Schema::dropIfExists('partner_integrations');
    Schema::dropIfExists('support_comments');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('affiliate_profiles');
    Schema::dropIfExists('lb_feedback_responses');
    Schema::dropIfExists('lb_bookings');
    Schema::dropIfExists('lb_coupon_redemptions');
    Schema::dropIfExists('lb_review_feedbacks');
    Schema::dropIfExists('lb_lead_submissions');
    Schema::dropIfExists('lb_qr_scans');
    Schema::dropIfExists('lb_campaigns');
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
        $table->unsignedBigInteger('next_plan_id')->nullable();
        $table->timestamp('plan_started_at')->nullable();
        $table->timestamp('plan_expires_at')->nullable();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->string('referral_code', 20)->nullable();
        $table->unsignedBigInteger('referred_by_user_id')->nullable();
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

    Schema::create('lb_campaigns', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('business_id')->nullable();
        $table->string('name');
        $table->string('slug')->nullable();
        $table->string('status', 40)->nullable();
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_qr_scans', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('campaign_id');
        $table->timestamp('created_at')->nullable();
    });

    Schema::create('lb_lead_submissions', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('campaign_id');
        $table->string('name')->nullable();
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_review_feedbacks', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('campaign_id');
        $table->unsignedTinyInteger('rating')->default(0);
        $table->string('customer_phone')->nullable();
        $table->string('customer_email')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_coupon_redemptions', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('campaign_id');
        $table->string('customer_phone')->nullable();
        $table->string('customer_email')->nullable();
        $table->timestamp('used_at')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_bookings', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('campaign_id');
        $table->string('customer_phone')->nullable();
        $table->string('customer_email')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_feedback_responses', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('campaign_id');
        $table->string('customer_phone')->nullable();
        $table->string('customer_email')->nullable();
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

    Schema::create('support_comments', function (Blueprint $table): void {
        $table->id();
        $table->string('id_secure', 40)->unique();
        $table->unsignedBigInteger('ticket_id');
        $table->unsignedBigInteger('user_id');
        $table->text('comment');
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

function crossHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Idempotency-Key' => (string) str()->uuid(),
        'Accept' => 'application/json',
    ], $overrides);
}

function crossOnboardingPayload(string $externalBusinessId, string $email): array
{
    return [
        'external_business_id' => $externalBusinessId,
        'external_user_id' => 'ext-'.$externalBusinessId,
        'marketing_goal_codes' => ['qr_checkin', 'customer_retention'],
        'package_code' => 'base',
        'owner' => [
            'name' => 'Owner '.$externalBusinessId,
            'phone' => '0901234567',
            'email' => $email,
        ],
        'business' => [
            'name' => 'Shop '.$externalBusinessId,
            'industry' => 'restaurant_food',
            'phone' => '0901234567',
            'email' => 'shop-'.$externalBusinessId.'@example.com',
            'website' => 'https://shop.example.com',
            'address' => 'Da Nang',
            'tax_code' => 'TAX-'.$externalBusinessId,
            'business_license_number' => 'LIC-'.$externalBusinessId,
        ],
        'verification' => [
            'identity_verified' => true,
            'verified_at' => '2026-07-13T10:00:00+07:00',
            'verified_by' => 'fizahub',
        ],
    ];
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);
    config()->set('modules.apipartnerfizahub.one_time_login_ttl_minutes', 5);
    createCrossEndpointTables();

    AdminPlan::query()->create([
        'name' => 'MLHUB Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'free_plan' => true,
        'default_signup_plan' => true,
        'currency' => 'VND',
        'price' => 0,
        'permissions' => [
            'max_businesses' => 1,
            'max_campaigns' => 5,
            'max_landing_pages' => 5,
            'max_qr_codes' => 10,
            'max_team_members' => 3,
        ],
    ]);
});

afterEach(function (): void {
    dropFizaHubPartnerTables();
    Schema::dropIfExists('support_comments');
    Schema::dropIfExists('support_tickets');
    Schema::dropIfExists('affiliate_profiles');
    Schema::dropIfExists('lb_feedback_responses');
    Schema::dropIfExists('lb_bookings');
    Schema::dropIfExists('lb_coupon_redemptions');
    Schema::dropIfExists('lb_review_feedbacks');
    Schema::dropIfExists('lb_lead_submissions');
    Schema::dropIfExists('lb_qr_scans');
    Schema::dropIfExists('lb_campaigns');
    Schema::dropIfExists('lb_businesses');
    Schema::dropIfExists('team_user');
    Schema::dropIfExists('teams');
    Schema::dropIfExists('users');
    Schema::dropIfExists('plans');
    Schema::dropIfExists('audit_logs');
});

test('cross-endpoint lifecycle keeps ids consistent and isolates tenants', function (): void {
    $requestId = (string) str()->uuid();

    $onboarding = $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        crossOnboardingPayload('biz-life', 'life@example.com'),
        crossHeaders(['X-Request-Id' => $requestId])
    )->assertCreated();

    expect($onboarding->json('data.status'))->toBe('awaiting_consultant')
        ->and($onboarding->json('data.request_id'))->toBe($requestId)
        ->and($onboarding->json('data.external_business_id'))->toBe('biz-life');

    $mlhubUserId = $onboarding->json('data.mlhub_user_id');
    $mlhubBusinessId = $onboarding->json('data.mlhub_business_id');

    $package = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-life/package',
        crossHeaders()
    )->assertOk();

    expect($package->json('data.effective_package'))->toBe('free')
        ->and($package->json('data.plan_slug'))->toBe('mlhub-free-da-nang')
        ->and($package->json('data'))->not->toHaveKey('price')
        ->and($package->json('data'))->not->toHaveKey('credits');

    $dashboard = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-life/dashboard?from=2026-07-01&to=2026-07-13',
        crossHeaders()
    )->assertOk();

    expect($dashboard->json('data.metrics.businesses'))->toBe(1)
        ->and($dashboard->json('data.metrics'))->toHaveKeys([
            'new_reviews',
            'returning_customers',
            'conversion_rate',
        ])
        ->and(json_encode($dashboard->json('data')))->not->toContain('TAX-biz-life');

    $ticketCreate = $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-life/support-tickets',
        ['subject' => 'Lifecycle help', 'message' => 'Please check mapping'],
        crossHeaders()
    )->assertCreated();

    $ticketId = $ticketCreate->json('data.ticket_id');
    expect($ticketId)->toBeString()->not->toBeEmpty();

    $list = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-life/support-tickets',
        crossHeaders()
    )->assertOk();

    expect($list->json('data.items.0.ticket_id'))->toBe($ticketId);

    $admin = User::query()->create([
        'name' => 'Admin',
        'username' => 'admin_'.Str::lower(Str::random(6)),
        'email' => 'admin-life@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'is_super_admin' => true,
    ]);

    $ticket = SupportTicket::query()->where('id_secure', $ticketId)->firstOrFail();
    SupportComment::query()->create([
        'id_secure' => Str::random(32),
        'ticket_id' => $ticket->id,
        'user_id' => $admin->id,
        'comment' => 'Admin lifecycle reply',
        'created' => time() + 5,
        'changed' => time() + 5,
    ]);

    $detail = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-life/support-tickets/'.$ticketId,
        crossHeaders()
    )->assertOk();

    expect(collect($detail->json('data.messages'))->pluck('sender_type')->all())
        ->toContain('business')
        ->toContain('admin');

    $message = $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-life/support-tickets/'.$ticketId.'/messages',
        ['message' => 'Thanks for the update'],
        crossHeaders()
    )->assertCreated();

    expect($message->json('data.ticket_id') ?? $ticketId)->not->toBeEmpty();

    markCrossOnboardingReady('biz-life');

    $login = $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-life/crm-login-links',
        [],
        crossHeaders(['Idempotency-Key' => (string) str()->uuid()])
    )->assertCreated();

    $loginUrl = $login->json('data.url');
    expect($loginUrl)->toContain('/partners/fizahub/one-time-login/');

    $integration = PartnerIntegration::query()->where('external_business_id', 'biz-life')->firstOrFail();
    expect($integration->mlhub_user_id)->toBe($mlhubUserId)
        ->and($integration->mlhub_business_id)->toBe($mlhubBusinessId)
        ->and(LocalBusiness::query()->whereKey($mlhubBusinessId)->exists())->toBeTrue();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        crossOnboardingPayload('biz-other', 'other@example.com'),
        crossHeaders()
    )->assertCreated();

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-other/support-tickets/'.$ticketId,
        crossHeaders()
    )->assertNotFound();

    $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-other/support-tickets/'.$ticketId.'/messages',
        ['message' => 'cross tenant'],
        crossHeaders()
    )->assertNotFound();

    $otherList = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-other/support-tickets',
        crossHeaders()
    )->assertOk();

    $otherItems = collect($otherList->json('data.items') ?? []);
    expect($otherItems->pluck('ticket_id')->all())->not->toContain($ticketId)
        ->and($otherItems)->not->toBeEmpty()
        ->and((string) $otherItems->first()['subject'])->toContain('onboarding');
});

test('partner api logs redact secrets and route surface stays within mvp', function (): void {
    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        array_replace_recursive(crossOnboardingPayload('biz-secure', 'secure@example.com'), [
            'owner' => [
                'password' => 'should-never-persist',
                'cccd' => '012345678901',
                'identity_document' => ['file' => 'raw'],
                'business_license_file' => 'raw-gpkd',
            ],
        ]),
        crossHeaders()
    )->assertStatus(422);

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        crossOnboardingPayload('biz-secure', 'secure@example.com'),
        crossHeaders()
    )->assertCreated();

    markCrossOnboardingReady('biz-secure');

    $login = $this->postJson(
        '/api/v1/partners/fizahub/businesses/biz-secure/crm-login-links',
        [],
        crossHeaders([
            'Authorization' => 'Bearer test-fizahub-partner-token',
            'Idempotency-Key' => (string) str()->uuid(),
        ])
    )->assertCreated();

    $plainUrl = $login->json('data.url');

    $encodedLogs = json_encode(PartnerApiLog::query()->get()->toArray());
    expect($encodedLogs)->not->toContain('test-fizahub-partner-token')
        ->and($encodedLogs)->not->toContain('should-never-persist')
        ->and($encodedLogs)->not->toContain('012345678901')
        ->and($encodedLogs)->not->toContain('raw-gpkd')
        ->and($encodedLogs)->not->toContain($plainUrl)
        ->and($encodedLogs)->not->toContain('Bearer test-fizahub-partner-token');

    $partnerApiRoutes = collect(Route::getRoutes())
        ->filter(fn ($route) => str_contains($route->uri(), 'partners/fizahub'))
        ->map(fn ($route) => strtoupper(implode('|', $route->methods())).' '.$route->uri())
        ->values()
        ->all();

    $expectedApi = [
        'GET|HEAD api/v1/partners/fizahub/health',
        'POST api/v1/partners/fizahub/partner/sso/verify',
        'GET|HEAD api/v1/partners/fizahub/marketing-catalog',
        'POST api/v1/partners/fizahub/onboarding-requests',
        'GET|HEAD api/v1/partners/fizahub/onboarding-requests/{request_id}',
        'GET|HEAD api/v1/partners/fizahub/businesses/{external_business_id}/marketing-status',
        'PATCH api/v1/partners/fizahub/businesses/{external_business_id}/profile',
        'PATCH api/v1/partners/fizahub/businesses/{external_business_id}/marketing-preferences',
        'GET|HEAD api/v1/partners/fizahub/businesses/{external_business_id}/dashboard',
        'GET|HEAD api/v1/partners/fizahub/businesses/{external_business_id}/growth-insights',
        'GET|HEAD api/v1/partners/fizahub/businesses/{external_business_id}/campaigns',
        'GET|HEAD api/v1/partners/fizahub/businesses/{external_business_id}/campaigns/{campaign_id}',
        'POST api/v1/partners/fizahub/businesses/{external_business_id}/campaigns/{campaign_id}/approval',
        'GET|HEAD api/v1/partners/fizahub/businesses/{external_business_id}/package',
        'GET|HEAD api/v1/partners/fizahub/businesses/{external_business_id}/support-presets',
        'POST api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets',
        'GET|HEAD api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets',
        'GET|HEAD api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}',
        'POST api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}/messages',
        'POST api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}/close',
        'POST api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}/reopen',
        'POST api/v1/partners/fizahub/businesses/{external_business_id}/crm-login-links',
        'GET|POST|HEAD partners/fizahub/one-time-login/{token}',
    ];

    expect($partnerApiRoutes)->toEqualCanonicalizing($expectedApi);

    foreach ([
        'customers',
        'chat',
        'ai-studio',
        'coupons',
        'landing',
        'google-business',
        'credits',
        'cccd',
    ] as $forbidden) {
        expect(collect($partnerApiRoutes)->contains(fn (string $route): bool => str_contains($route, $forbidden)))
            ->toBeFalse();
    }

    expect(Route::has('partner.fizahub.login.consume'))->toBeTrue();
});
