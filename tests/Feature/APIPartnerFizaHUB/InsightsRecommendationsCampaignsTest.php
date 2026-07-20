<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppQRCampaigns\Models\QrScan;

require_once __DIR__.'/FizaHubTestHelpers.php';

function createCampaignInsightsTables(): void
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
        $table->boolean('free_plan')->default(false);
        $table->decimal('price', 16, 2)->default(0);
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

    createFizaHubPartnerTables();
}

/**
 * @return array{user: User, business: LocalBusiness, integration: PartnerIntegration}
 */
function seedCampaignBusiness(string $externalBusinessId, string $email): array
{
    $plan = AdminPlan::query()->firstOrCreate(
        ['slug' => 'mlhub-free-da-nang'],
        ['name' => 'MLHUB Free Da Nang', 'status' => true, 'free_plan' => true, 'price' => 0, 'permissions' => []]
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

function campaignHeaders(): array
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
    config()->set('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
    createCampaignInsightsTables();
});

afterEach(function (): void {
    dropFizaHubPartnerTables();
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

test('growth insights returns zero-data payload with deterministic highlights when no growth data exists', function (): void {
    seedCampaignBusiness('biz-insights-empty', 'insights-empty@example.com');

    $response = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-insights-empty/growth-insights?from=2026-07-01&to=2026-07-03',
        campaignHeaders()
    )->assertOk();

    $response->assertJsonPath('data.growth_score', 0)
        ->assertJsonCount(3, 'data.highlights');

    expect(collect($response->json('data.highlights'))->pluck('code')->all())
        ->toBe(['period_scans', 'period_leads', 'period_conversion']);
});

test('growth insights returns 404 integration_not_found for an unmapped business', function (): void {
    $this->getJson(
        '/api/v1/partners/fizahub/businesses/unknown-insights/growth-insights',
        campaignHeaders()
    )->assertNotFound()->assertJsonPath('error.code', 'integration_not_found');
});

test('growth insights rejects an invalid date range the same way as the dashboard', function (): void {
    seedCampaignBusiness('biz-insights-badrange', 'insights-badrange@example.com');

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-insights-badrange/growth-insights?from=2026-07-10&to=2026-07-01',
        campaignHeaders()
    )->assertStatus(422)->assertJsonPath('error.code', 'validation_failed');
});

test('growth insights folds recommendations into one endpoint and scopes missing businesses', function (): void {
    seedCampaignBusiness('biz-reco-empty', 'reco-empty@example.com');

    $response = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-reco-empty/growth-insights?from=2026-07-01&to=2026-07-03',
        campaignHeaders()
    )->assertOk();

    expect(collect($response->json('data.recommendations'))->pluck('code')->all())
        ->toContain('create_campaign');

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/unknown-reco/growth-insights',
        campaignHeaders()
    )->assertNotFound()->assertJsonPath('error.code', 'integration_not_found');
});

test('campaigns list returns an empty array when the business has no campaigns, never a 500', function (): void {
    seedCampaignBusiness('biz-campaigns-empty', 'campaigns-empty@example.com');

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-campaigns-empty/campaigns',
        campaignHeaders()
    )->assertOk()->assertJsonPath('data.items', []);
});

test('campaigns list and detail are tenant scoped, and a campaign belonging to another business is not found', function (): void {
    $a = seedCampaignBusiness('biz-camp-a', 'camp-a@example.com');
    $b = seedCampaignBusiness('biz-camp-b', 'camp-b@example.com');

    $campaignA = QrCampaign::withoutEvents(fn () => QrCampaign::query()->create([
        'user_id' => $a['user']->id,
        'business_id' => $a['business']->id,
        'name' => 'Campaign A',
        'slug' => 'campaign-a',
        'status' => 'active',
        'published_at' => now(),
    ]));

    $campaignB = QrCampaign::withoutEvents(fn () => QrCampaign::query()->create([
        'user_id' => $b['user']->id,
        'business_id' => $b['business']->id,
        'name' => 'Campaign B',
        'slug' => 'campaign-b',
        'status' => 'active',
        'published_at' => now(),
    ]));

    QrScan::withoutEvents(fn () => QrScan::query()->create([
        'user_id' => $a['user']->id,
        'campaign_id' => $campaignA->id,
        'created_at' => now(),
    ]));

    // A's list must only contain A's campaign.
    $listA = $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-camp-a/campaigns',
        campaignHeaders()
    )->assertOk();

    expect(collect($listA->json('data.items'))->pluck('campaign_id')->all())
        ->toBe([$campaignA->id]);

    // A can fetch its own campaign detail.
    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-camp-a/campaigns/'.$campaignA->id,
        campaignHeaders()
    )->assertOk()->assertJsonPath('data.campaign.campaign_id', $campaignA->id);

    // A must NOT be able to fetch B's campaign by id — typed 404, not a leak, not a 500.
    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-camp-a/campaigns/'.$campaignB->id,
        campaignHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'campaign_not_found');
});

test('campaign detail with a non-numeric or empty-looking campaign_id returns a typed 404, never a 500', function (): void {
    seedCampaignBusiness('biz-camp-badid', 'camp-badid@example.com');

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-camp-badid/campaigns/not-a-numeric-id',
        campaignHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'campaign_not_found');

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-camp-badid/campaigns/%7B%7Bcampaign_id%7D%7D',
        campaignHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'campaign_not_found');
});
