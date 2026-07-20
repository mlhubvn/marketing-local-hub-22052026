<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppQRCampaigns\Models\QrCampaign;

require_once __DIR__.'/FizaHubTestHelpers.php';

function growthUiHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) Str::uuid(),
        'Idempotency-Key' => (string) Str::uuid(),
        'Accept' => 'application/json',
    ], $overrides);
}

function createGrowthUiTables(): void
{
    foreach (['lb_feedback_responses', 'lb_bookings', 'lb_coupon_redemptions', 'lb_review_feedbacks', 'lb_lead_submissions', 'lb_qr_scans', 'lb_campaigns'] as $table) {
        Schema::dropIfExists($table);
    }

    Schema::create('lb_campaigns', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->unsignedBigInteger('business_id')->nullable();
        $table->string('name');
        $table->string('slug')->nullable();
        $table->string('status', 40)->nullable();
        $table->string('objective')->nullable();
        $table->json('settings')->nullable();
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

    foreach (['lb_bookings', 'lb_feedback_responses'] as $tableName) {
        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('campaign_id');
            $table->string('customer_phone')->nullable();
            $table->string('customer_email')->nullable();
            $table->timestamps();
        });
    }
}

/** @return array{user: User, business: LocalBusiness, integration: PartnerIntegration} */
function seedGrowthUiBusiness(): array
{
    $plan = AdminPlan::query()->create([
        'name' => 'MLHUB Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'free_plan' => true,
        'default_signup_plan' => true,
        'currency' => 'VND',
        'price' => 0,
        'permissions' => ['max_campaigns' => 5],
    ]);
    $user = User::query()->create([
        'name' => 'Growth Owner',
        'username' => 'growthowner',
        'email' => 'growth-owner@example.com',
        'password' => 'not-used-by-test',
        'plan_id' => $plan->id,
        'plan_started_at' => now()->subDay(),
    ]);
    $team = Team::query()->create([
        'name' => 'Growth Team',
        'slug' => 'growth-team',
        'owner_user_id' => $user->id,
    ]);
    $business = LocalBusiness::query()->create([
        'user_id' => $user->id,
        'name' => 'Growth Store',
        'type' => 'Restaurant',
    ]);
    $integration = PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'biz-growth',
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'package_code' => 'free',
        'status' => 'active',
    ]);

    return compact('user', 'business', 'integration');
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);
    config()->set('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
    bootProductionLikeSchema();
    createGrowthUiTables();
});

afterEach(function (): void {
    foreach (['lb_feedback_responses', 'lb_bookings', 'lb_coupon_redemptions', 'lb_review_feedbacks', 'lb_lead_submissions', 'lb_qr_scans', 'lb_campaigns'] as $table) {
        Schema::dropIfExists($table);
    }
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

test('dashboard defaults to 30d accepts presets and validates custom ranges', function (): void {
    seedGrowthUiBusiness();
    $url = '/api/v1/partners/fizahub/businesses/biz-growth/dashboard';

    $this->getJson($url, growthUiHeaders())
        ->assertOk()
        ->assertJsonPath('data.period.range', '30d')
        ->assertJsonPath('data.metrics.new_customers', 0)
        ->assertJsonPath('data.new_customers', 0)
        ->assertJsonPath('data.qr_scans', 0)
        ->assertJsonPath('data.rating', 0);

    foreach (['today', '7d', '30d', '90d'] as $range) {
        $this->getJson($url.'?range='.$range, growthUiHeaders())
            ->assertOk()
            ->assertJsonPath('data.period.range', $range);
    }

    $this->getJson($url.'?range=custom&from=2026-01-01&to=2027-01-03', growthUiHeaders())
        ->assertUnprocessable()->assertJsonPath('error.code', 'validation_failed');
    $this->getJson($url.'?range=custom&from=2026-02-02', growthUiHeaders())
        ->assertUnprocessable()->assertJsonPath('error.code', 'validation_failed');
    $this->getJson($url.'?range=custom&from=2026-02-03&to=2026-02-02', growthUiHeaders())
        ->assertUnprocessable()->assertJsonPath('error.code', 'validation_failed');
});

test('growth insights combines scores sources highlights recommendations and freshness', function (): void {
    seedGrowthUiBusiness();

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-growth/growth-insights?range=30d',
        growthUiHeaders()
    )->assertOk()
        ->assertJsonStructure(['data' => [
            'growth_score', 'growth_score_change', 'customer_sources', 'highlights',
            'recommendations', 'data_period', 'data_freshness',
        ]])
        ->assertJsonPath('data.recommendations.0.cta.action_type', 'create_support_ticket')
        ->assertJsonPath('data.recommendations.0.cta.preset_code', 'growth_recommendation');
});

test('campaign list uses status search and cursor pagination with zero-data success', function (): void {
    $seed = seedGrowthUiBusiness();

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-growth/campaigns',
        growthUiHeaders()
    )->assertOk()
        ->assertJsonPath('data.items', [])
        ->assertJsonPath('data.pagination.has_more', false)
        ->assertJsonPath('data.pagination.per_page', 20);

    QrCampaign::withoutEvents(fn () => QrCampaign::query()->create([
        'user_id' => $seed['user']->id,
        'business_id' => $seed['business']->id,
        'name' => 'Paused Summer Campaign',
        'slug' => 'paused-summer',
        'status' => 'paused',
        'published_at' => now(),
    ]));
    QrCampaign::withoutEvents(fn () => QrCampaign::query()->create([
        'user_id' => $seed['user']->id,
        'business_id' => $seed['business']->id,
        'name' => 'Active QR Campaign',
        'slug' => 'active-qr',
        'status' => 'active',
    ]));

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-growth/campaigns?status=active&search=QR&per_page=1',
        growthUiHeaders()
    )->assertOk()
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.status', 'active')
        ->assertJsonStructure(['data' => ['items', 'summary', 'pagination' => [
            'next_cursor', 'has_more', 'per_page',
        ]]])
        ->assertJsonPath('data.pagination.per_page', 1);

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-growth/campaigns?cursor=not-valid',
        growthUiHeaders()
    )->assertUnprocessable()->assertJsonPath('error.code', 'validation_failed');
});

test('campaign detail follows UI shape and status column instead of published timestamp', function (): void {
    $seed = seedGrowthUiBusiness();
    $campaign = QrCampaign::withoutEvents(fn () => QrCampaign::query()->create([
        'user_id' => $seed['user']->id,
        'business_id' => $seed['business']->id,
        'name' => 'Paused Campaign',
        'slug' => 'paused-campaign',
        'status' => 'paused',
        'objective' => 'Thu hút khách quay lại',
        'published_at' => now(),
    ]));

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/biz-growth/campaigns/'.$campaign->id.'?range=30d',
        growthUiHeaders()
    )->assertOk()
        ->assertJsonPath('data.campaign_id', $campaign->id)
        ->assertJsonPath('data.status', 'paused')
        ->assertJsonPath('data.objective', 'Thu hút khách quay lại')
        ->assertJsonStructure(['data' => [
            'campaign_id', 'name', 'status', 'objective', 'period', 'metrics',
            'qr_scans', 'qr_scan_change', 'valid_leads', 'conversion_rate',
            'last_synced_at', 'recommendations',
        ]]);
});

test('campaign approval is idempotent and rejects an invalid state transition', function (): void {
    $seed = seedGrowthUiBusiness();
    $campaign = QrCampaign::withoutEvents(fn () => QrCampaign::query()->create([
        'user_id' => $seed['user']->id,
        'business_id' => $seed['business']->id,
        'name' => 'Approval Campaign',
        'slug' => 'approval-campaign',
        'status' => 'pending_approval',
        'settings' => [],
    ]));
    $url = '/api/v1/partners/fizahub/businesses/biz-growth/campaigns/'.$campaign->id.'/approval';
    $headers = growthUiHeaders(['Idempotency-Key' => 'approve-campaign-1']);

    $first = $this->postJson($url, ['decision' => 'approved', 'note' => 'Đã kiểm tra'], $headers)
        ->assertOk()
        ->assertJsonPath('data.status', 'active');
    $second = $this->postJson($url, ['decision' => 'approved', 'note' => 'Đã kiểm tra'], $headers)
        ->assertOk();

    expect($second->json('data'))->toBe($first->json('data'))
        ->and($campaign->refresh()->status)->toBe('active')
        ->and($campaign->settings['partner_approval']['decision'])->toBe('approved');

    $this->postJson(
        $url,
        ['decision' => 'approved'],
        growthUiHeaders(['Idempotency-Key' => 'approve-campaign-2'])
    )->assertConflict()->assertJsonPath('error.code', 'campaign_invalid_state');
});

test('changes requested reuses one campaign support ticket', function (): void {
    $seed = seedGrowthUiBusiness();
    $campaign = QrCampaign::withoutEvents(fn () => QrCampaign::query()->create([
        'user_id' => $seed['user']->id,
        'business_id' => $seed['business']->id,
        'name' => 'Campaign Needs Changes',
        'slug' => 'campaign-needs-changes',
        'status' => 'pending_approval',
        'settings' => [],
    ]));
    $url = '/api/v1/partners/fizahub/businesses/biz-growth/campaigns/'.$campaign->id.'/approval';

    $first = $this->postJson(
        $url,
        ['decision' => 'changes_requested', 'note' => 'Cần đổi ưu đãi'],
        growthUiHeaders(['Idempotency-Key' => 'changes-1'])
    )->assertOk();
    $second = $this->postJson(
        $url,
        ['decision' => 'changes_requested', 'note' => 'Cần đổi ưu đãi'],
        growthUiHeaders(['Idempotency-Key' => 'changes-2'])
    )->assertOk();

    expect($first->json('data.support_ticket_id'))->not->toBeEmpty()
        ->and($second->json('data.support_ticket_id'))->toBe($first->json('data.support_ticket_id'))
        ->and(SupportTicket::query()->count())->toBe(1)
        ->and($campaign->refresh()->status)->toBe('pending_approval');
});
