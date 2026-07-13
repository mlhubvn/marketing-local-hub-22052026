<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppQRCampaigns\Models\QrScan;
use Modules\AppReviewBooster\Models\ReviewFeedback;

function createDashboardApiTables(): void
{
    Schema::dropIfExists('partner_one_time_logins');
    Schema::dropIfExists('partner_api_logs');
    Schema::dropIfExists('partner_onboarding_requests');
    Schema::dropIfExists('partner_integrations');
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

    $migration = require base_path('modules/APIPartnerFizaHUB/Database/Migrations/2026_07_13_000000_create_fizahub_partner_api_tables.php');
    $migration->up();
}

/**
 * @return array{user: User, business: LocalBusiness, integration: PartnerIntegration}
 */
function seedDashboardBusiness(string $externalBusinessId, string $email): array
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
    ]);

    $team = Team::query()->create([
        'name' => $user->name.' Team',
        'slug' => 'team-'.$user->id.'-'.Str::lower(Str::random(4)),
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

    return compact('user', 'business', 'integration');
}

function dashboardHeaders(array $overrides = []): array
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
    config()->set('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
    createDashboardApiTables();
});

afterEach(function (): void {
    Schema::dropIfExists('partner_one_time_logins');
    Schema::dropIfExists('partner_api_logs');
    Schema::dropIfExists('partner_onboarding_requests');
    Schema::dropIfExists('partner_integrations');
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
});

test('dashboard defaults to last 30 days and rejects invalid ranges', function (): void {
    seedDashboardBusiness('biz-dash-dates', 'dates@example.com');

    $this->travelTo(CarbonImmutable::parse('2026-07-13 10:00:00', 'Asia/Ho_Chi_Minh'));

    $default = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-dash-dates/dashboard',
        dashboardHeaders()
    )->assertOk();

    expect($default->json('data.period.from'))->toBe('2026-06-14')
        ->and($default->json('data.period.to'))->toBe('2026-07-13')
        ->and($default->json('data.period.timezone'))->toBe('Asia/Ho_Chi_Minh')
        ->and($default->json('data.trend'))->toHaveCount(30);

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-dash-dates/dashboard?from=2026-07-10&to=2026-07-01',
        dashboardHeaders()
    )
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'validation_failed');

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-dash-dates/dashboard?from=2025-01-01&to=2026-01-03',
        dashboardHeaders()
    )
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'validation_failed');

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-dash-dates/dashboard?from=not-a-date&to=2026-07-01',
        dashboardHeaders()
    )
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'validation_failed');
});

test('dashboard returns zeroed metrics shape when no growth data exists', function (): void {
    seedDashboardBusiness('biz-empty', 'empty@example.com');

    $response = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-empty/dashboard?from=2026-07-01&to=2026-07-03',
        dashboardHeaders()
    )->assertOk();

    $metrics = $response->json('data.metrics');
    expect(array_keys($metrics))->toBe([
        'businesses',
        'campaigns',
        'active_campaigns',
        'qr_scans',
        'new_leads',
        'new_reviews',
        'coupon_claims',
        'coupon_used',
        'bookings',
        'feedback',
        'returning_customers',
        'conversion_rate',
    ]);

    expect($metrics)->toMatchArray([
        'businesses' => 1,
        'campaigns' => 0,
        'active_campaigns' => 0,
        'qr_scans' => 0,
        'new_leads' => 0,
        'new_reviews' => 0,
        'coupon_claims' => 0,
        'coupon_used' => 0,
        'bookings' => 0,
        'feedback' => 0,
        'returning_customers' => 0,
        'conversion_rate' => 0.0,
    ]);

    expect($response->json('data.campaigns'))->toBe([])
        ->and($response->json('data.trend'))->toHaveCount(3)
        ->and($response->json('data.insights'))->not->toBeEmpty()
        ->and(collect($response->json('data.suggested_actions'))->pluck('code')->all())
        ->toContain('create_campaign');
});

test('dashboard metrics are tenant scoped and use documented mvp definitions', function (): void {
    $a = seedDashboardBusiness('biz-a', 'a-dash@example.com');
    $b = seedDashboardBusiness('biz-b', 'b-dash@example.com');

    $day = CarbonImmutable::parse('2026-07-10 09:00:00', 'Asia/Ho_Chi_Minh');
    $outside = CarbonImmutable::parse('2026-06-01 09:00:00', 'Asia/Ho_Chi_Minh');

    [$campaignA, $pausedA, $campaignB] = QrCampaign::withoutEvents(function () use ($a, $b, $day) {
        return [
            QrCampaign::query()->create([
                'user_id' => $a['user']->id,
                'business_id' => $a['business']->id,
                'name' => 'Campaign A',
                'slug' => 'campaign-a',
                'status' => 'active',
                'published_at' => $day,
            ]),
            QrCampaign::query()->create([
                'user_id' => $a['user']->id,
                'business_id' => $a['business']->id,
                'name' => 'Paused A',
                'slug' => 'paused-a',
                'status' => 'paused',
                'published_at' => null,
            ]),
            QrCampaign::query()->create([
                'user_id' => $b['user']->id,
                'business_id' => $b['business']->id,
                'name' => 'Campaign B',
                'slug' => 'campaign-b',
                'status' => 'active',
                'published_at' => $day,
            ]),
        ];
    });

    // 10 scans for A, 50 for B (must not leak).
    QrScan::withoutEvents(function () use ($a, $b, $campaignA, $campaignB, $pausedA, $day): void {
        foreach (range(1, 10) as $i) {
            QrScan::query()->create([
                'user_id' => $a['user']->id,
                'campaign_id' => $campaignA->id,
                'created_at' => $day->addMinutes($i),
            ]);
        }
        foreach (range(1, 50) as $i) {
            QrScan::query()->create([
                'user_id' => $b['user']->id,
                'campaign_id' => $campaignB->id,
                'created_at' => $day->addMinutes($i),
            ]);
        }
        QrScan::query()->create([
            'user_id' => $a['user']->id,
            'campaign_id' => $pausedA->id,
            'created_at' => $day,
        ]);
    });

    LeadSubmission::withoutEvents(function () use ($a, $campaignA, $day, $outside): void {
        LeadSubmission::query()->create([
            'user_id' => $a['user']->id,
            'campaign_id' => $campaignA->id,
            'name' => 'Lead 1',
            'phone' => '0901111111',
            'email' => 'lead@example.com',
            'created_at' => $day,
        ]);
        LeadSubmission::query()->create([
            'user_id' => $a['user']->id,
            'campaign_id' => $campaignA->id,
            'name' => 'Lead 2 returning',
            'phone' => '0901-111-111',
            'email' => null,
            'created_at' => $day->addHour(),
        ]);
        LeadSubmission::query()->create([
            'user_id' => $a['user']->id,
            'campaign_id' => $campaignA->id,
            'name' => 'Outside range',
            'phone' => '0909999999',
            'created_at' => $outside,
        ]);
    });

    // MVP new_reviews: internal ReviewFeedback rating >= 4 (not Google Reviews).
    ReviewFeedback::withoutEvents(function () use ($a, $campaignA, $day): void {
        ReviewFeedback::query()->create([
            'user_id' => $a['user']->id,
            'campaign_id' => $campaignA->id,
            'rating' => 5,
            'customer_phone' => '0902222222',
            'created_at' => $day,
        ]);
        ReviewFeedback::query()->create([
            'user_id' => $a['user']->id,
            'campaign_id' => $campaignA->id,
            'rating' => 2,
            'customer_phone' => '0903333333',
            'created_at' => $day,
        ]);
    });

    CouponRedemption::withoutEvents(function () use ($a, $campaignA, $day): void {
        CouponRedemption::query()->create([
            'user_id' => $a['user']->id,
            'campaign_id' => $campaignA->id,
            'customer_phone' => '0904444444',
            'used_at' => $day,
            'created_at' => $day,
        ]);
        CouponRedemption::query()->create([
            'user_id' => $a['user']->id,
            'campaign_id' => $campaignA->id,
            'customer_phone' => '0905555555',
            'used_at' => null,
            'created_at' => $day,
        ]);
    });

    Booking::withoutEvents(function () use ($a, $campaignA, $day): void {
        Booking::query()->create([
            'user_id' => $a['user']->id,
            'campaign_id' => $campaignA->id,
            'customer_phone' => '0906666666',
            'created_at' => $day,
        ]);
    });

    FeedbackResponse::withoutEvents(function () use ($a, $campaignA, $day): void {
        FeedbackResponse::query()->create([
            'user_id' => $a['user']->id,
            'campaign_id' => $campaignA->id,
            'customer_phone' => '0907777777',
            'created_at' => $day,
        ]);
    });

    $response = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-a/dashboard?from=2026-07-09&to=2026-07-11',
        dashboardHeaders()
    )->assertOk();

    $metrics = $response->json('data.metrics');

    // conversions = leads(2) + reviews(1) + coupon_claims(2) + bookings(1) + feedback(forms1 + low1=2) = 8
    // scans in range for campaigns of biz-a = 10 (active) + 1 (paused) = 11
    expect($metrics['businesses'])->toBe(1)
        ->and($metrics['campaigns'])->toBe(2)
        ->and($metrics['active_campaigns'])->toBe(1)
        ->and($metrics['qr_scans'])->toBe(11)
        ->and($metrics['new_leads'])->toBe(2)
        ->and($metrics['new_reviews'])->toBe(1)
        ->and($metrics['coupon_claims'])->toBe(2)
        ->and($metrics['coupon_used'])->toBe(1)
        ->and($metrics['bookings'])->toBe(1)
        ->and($metrics['feedback'])->toBe(2)
        // Estimated returning_customers: phone/email identities with >= 2 events in period.
        ->and($metrics['returning_customers'])->toBe(1)
        ->and($metrics['conversion_rate'])->toBe(72.73);

    expect($metrics['qr_scans'])->not->toBe(50)
        ->and($metrics['qr_scans'])->not->toBe(61);

    $actions = collect($response->json('data.suggested_actions'))->pluck('code')->all();
    expect($actions)->toContain('follow_up_leads')
        ->and($actions)->toContain('reply_feedback')
        ->and($actions)->not->toContain('create_campaign');

    $trend = collect($response->json('data.trend'));
    expect($trend)->toHaveCount(3)
        ->and($trend->first()['date'])->toBe('2026-07-09')
        ->and($trend->last()['date'])->toBe('2026-07-11');

    $dayRow = $trend->firstWhere('date', '2026-07-10');
    expect($dayRow['scans'])->toBe(11)
        ->and($dayRow['leads'])->toBe(2)
        ->and($dayRow['reviews'])->toBe(1)
        ->and($dayRow['coupons'])->toBe(2)
        ->and($dayRow['bookings'])->toBe(1)
        ->and($dayRow['feedback'])->toBe(2);

    $campaigns = $response->json('data.campaigns');
    expect($campaigns)->toHaveCount(2)
        ->and($campaigns[0]['conversions'])->toBeGreaterThanOrEqual($campaigns[1]['conversions']);
});

test('dashboard excludes other business data and suggests improve_conversion when applicable', function (): void {
    $seed = seedDashboardBusiness('biz-conv', 'conv@example.com');
    $day = CarbonImmutable::parse('2026-07-12 12:00:00', 'Asia/Ho_Chi_Minh');

    $campaign = QrCampaign::withoutEvents(fn () => QrCampaign::query()->create([
        'user_id' => $seed['user']->id,
        'business_id' => $seed['business']->id,
        'name' => 'Low conversion',
        'slug' => 'low-conversion',
        'published_at' => $day,
    ]));

    QrScan::withoutEvents(function () use ($seed, $campaign, $day): void {
        foreach (range(1, 20) as $i) {
            QrScan::query()->create([
                'user_id' => $seed['user']->id,
                'campaign_id' => $campaign->id,
                'created_at' => $day->addMinutes($i),
            ]);
        }
    });

    $response = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-conv/dashboard?from=2026-07-12&to=2026-07-12',
        dashboardHeaders()
    )->assertOk();

    expect($response->json('data.metrics.qr_scans'))->toBe(20)
        ->and((float) $response->json('data.metrics.conversion_rate'))->toBe(0.0)
        ->and(collect($response->json('data.suggested_actions'))->pluck('code')->all())
        ->toContain('improve_conversion')
        ->and(collect($response->json('data.suggested_actions'))->pluck('code')->all())
        ->not->toContain('share_qr');
});
