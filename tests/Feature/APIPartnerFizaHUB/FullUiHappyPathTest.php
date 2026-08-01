<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerPackageAssignment;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportTicketContext;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppQRCampaigns\Models\QrCampaign;

require_once __DIR__.'/FizaHubTestHelpers.php';

function fullUiHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) Str::uuid(),
        'Idempotency-Key' => (string) Str::uuid(),
        'Accept' => 'application/json',
    ], $overrides);
}

function createFullUiGrowthTables(): void
{
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

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 600);
    config()->set('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
    config()->set('modules.apipartnerfizahub.one_time_login_ttl_minutes', 5);
    bootProductionLikeSchema();
    AdminPlan::query()->create([
        'name' => 'MKT Free Da Nang',
        'slug' => 'mlhub-free-da-nang',
        'status' => true,
        'free_plan' => true,
        'default_signup_plan' => true,
        'currency' => 'VND',
        'price' => 0,
        'permissions' => ['max_campaigns' => 5],
    ]);
    createFullUiGrowthTables();
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

test('full 15-screen FizaHUB UI path is sequential and second onboarding creates no duplicates', function (): void {
    $base = '/api/v1/partners/fizahub';
    $externalBusinessId = 'fiza-full-ui-001';
    $businessBase = $base.'/businesses/'.$externalBusinessId;
    $payload = [
        'external_business_id' => $externalBusinessId,
        'external_user_id' => 'fiza-owner-001',
        'marketing_goal_codes' => ['qr_checkin', 'customer_retention'],
        'package_code' => 'base',
        'owner' => [
            'name' => 'Đoàn Văn Khoa',
            'email' => 'van-khoa.full-ui@example.com',
        ],
        'business' => [
            'name' => 'Fiza Full UI Store',
            'industry' => 'restaurant_food',
            'phone' => '0901234888',
            'email' => 'contact.full-ui@example.com',
            'website' => 'https://full-ui.example.com',
            'address' => '888 Lê Duẩn, Đà Nẵng',
        ],
    ];

    $this->getJson($businessBase.'/marketing-status', fullUiHeaders())
        ->assertNotFound()
        ->assertJsonPath('error.code', 'integration_not_found')
        ->assertJsonPath('error.details.next_action', 'create_onboarding_request');

    $this->getJson($base.'/marketing-catalog?industry=restaurant_food', fullUiHeaders())
        ->assertOk()
        ->assertJsonStructure(['data' => ['marketing_goals', 'industries', 'packages']]);

    $onboarding = $this->postJson($base.'/onboarding-requests', $payload, fullUiHeaders())
        ->assertCreated()
        ->assertJsonPath('data.already_registered', false);
    $requestId = (string) $onboarding->json('data.request_id');
    $onboardingTicketId = (string) $onboarding->json('data.support_ticket_id');
    $mlhubUserId = (int) $onboarding->json('data.mlhub_user_id');
    $mlhubBusinessId = (int) $onboarding->json('data.mlhub_business_id');
    expect($requestId)->not->toBeEmpty()->and($onboardingTicketId)->not->toBeEmpty();

    $campaign = QrCampaign::withoutEvents(fn () => QrCampaign::query()->create([
        'user_id' => $mlhubUserId,
        'business_id' => $mlhubBusinessId,
        'name' => 'Fiza Pending Campaign',
        'slug' => 'fiza-pending-campaign',
        'status' => 'pending_approval',
        'objective' => 'Tăng khách quay lại',
        'settings' => [],
    ]));

    $this->getJson($businessBase.'/marketing-status', fullUiHeaders())
        ->assertOk()
        ->assertJsonPath('data.external_business_id', $externalBusinessId)
        ->assertJsonPath('data.onboarding_request_id', $requestId)
        ->assertJsonPath('data.support_ticket_id', $onboardingTicketId);

    $this->getJson($base.'/onboarding-requests/'.$requestId, fullUiHeaders())
        ->assertOk()
        ->assertJsonCount(5, 'data.timeline');

    $this->patchJson($businessBase.'/profile', [
        'owner' => ['name' => 'Đoàn Văn Khoa Updated'],
        'business' => [
            'name' => 'Fiza Full UI Store Updated',
            'industry' => 'restaurant_food',
            'phone' => '0909999888',
            'email' => 'updated.full-ui@example.com',
            'website' => 'https://updated.full-ui.example.com',
            'address' => '999 Lê Duẩn, Đà Nẵng',
        ],
    ], fullUiHeaders())->assertOk();

    $this->patchJson($businessBase.'/marketing-preferences', [
        'marketing_goal_codes' => ['qr_checkin', 'customer_retention'],
        'requested_package_code' => 'base',
    ], fullUiHeaders())
        ->assertOk()
        ->assertJsonPath('data.requested_package_code', 'base');

    $this->getJson($businessBase.'/dashboard?range=today', fullUiHeaders())
        ->assertOk()->assertJsonPath('data.period.range', 'today');
    $this->getJson($businessBase.'/dashboard?range=30d', fullUiHeaders())
        ->assertOk()->assertJsonPath('data.period.range', '30d');
    $this->getJson($businessBase.'/growth-insights?range=30d', fullUiHeaders())
        ->assertOk()->assertJsonStructure(['data' => ['growth_score', 'recommendations']]);

    $campaignList = $this->getJson($businessBase.'/campaigns?per_page=20', fullUiHeaders())
        ->assertOk();
    $campaignId = (string) data_get($campaignList->json(), 'data.items.0.campaign_id');
    expect($campaignId)->toBe((string) $campaign->id);

    $campaignDetail = $this->getJson($businessBase.'/campaigns/'.$campaignId, fullUiHeaders())
        ->assertOk();
    expect((string) $campaignDetail->json('data.campaign_id'))->toBe($campaignId);
    $this->postJson($businessBase.'/campaigns/'.$campaignId.'/approval', [
        'decision' => 'approved',
        'note' => 'Product approved.',
    ], fullUiHeaders())
        ->assertOk()->assertJsonPath('data.status', 'active');

    $this->getJson($businessBase.'/package', fullUiHeaders())
        ->assertOk()
        ->assertJsonPath('data.effective_package', 'free')
        ->assertJsonPath('data.requested_package', 'base');

    $this->getJson($businessBase.'/support-presets', fullUiHeaders())
        ->assertOk()->assertJsonPath('data.items.0.preset_code', 'qr_scan_not_recorded');
    $supportList = $this->getJson($businessBase.'/support-tickets', fullUiHeaders())->assertOk();
    expect(collect($supportList->json('data.items'))->pluck('ticket_type')->all())->toContain('onboarding');

    $ticket = $this->postJson($businessBase.'/support-tickets', [
        'preset_code' => 'qr_scan_not_recorded',
        'campaign_id' => $campaignId,
        'subject' => 'Client subject',
        'message' => 'QR chưa cập nhật lượt quét.',
        'response_channel' => 'in_app',
        'metadata' => ['screen' => 'growth_marketing', 'source' => 'fizahub'],
    ], fullUiHeaders())->assertCreated();
    $ticketId = (string) $ticket->json('data.ticket_id');
    expect($ticketId)->not->toBeEmpty()->not->toBe($onboardingTicketId);

    $this->getJson($businessBase.'/support-tickets/'.$ticketId, fullUiHeaders())
        ->assertOk()->assertJsonPath('data.ticket_id', $ticketId);
    $this->postJson($businessBase.'/support-tickets/'.$ticketId.'/messages', [
        'message' => 'Bổ sung thông tin text.',
    ], fullUiHeaders())->assertCreated();
    $this->postJson($businessBase.'/support-tickets/'.$ticketId.'/close', [], fullUiHeaders())
        ->assertOk()->assertJsonPath('data.status', 'closed');
    $this->postJson($businessBase.'/support-tickets/'.$ticketId.'/reopen', [], fullUiHeaders())
        ->assertOk()->assertJsonPath('data.status', 'open');

    $onboardingRow = PartnerOnboardingRequest::query()->where('request_id', $requestId)->firstOrFail();
    $onboardingRow->forceFill([
        'status' => OnboardingStatusMachine::READY,
        'admin_status' => OnboardingStatusMachine::READY,
        'current_step' => OnboardingStatusMachine::defaultStepFor(OnboardingStatusMachine::READY),
        'ready_at' => now(),
    ])->save();
    $this->postJson($businessBase.'/crm-login-links', [], fullUiHeaders())
        ->assertCreated()->assertJsonStructure(['data' => ['url', 'expires_at', 'expires_in_seconds']]);

    $countsBeforeRepeat = [
        'users' => User::query()->count(),
        'teams' => Team::query()->count(),
        'businesses' => LocalBusiness::query()->count(),
        'integrations' => PartnerIntegration::query()->count(),
        'active_assignments' => PartnerPackageAssignment::query()->where('status', 'active')->count(),
        'onboarding_requests' => PartnerOnboardingRequest::query()->count(),
        'onboarding_tickets' => PartnerSupportTicketContext::query()->where('request_code', 'fizahub_onboarding')->count(),
        'support_tickets' => SupportTicket::query()->count(),
    ];

    $repeat = $this->postJson($base.'/onboarding-requests', $payload, fullUiHeaders())
        ->assertOk()
        ->assertJsonPath('data.already_registered', true)
        ->assertJsonPath('data.request_id', $requestId)
        ->assertJsonPath('data.support_ticket_id', $onboardingTicketId);
    expect($repeat->json('data.username'))->toMatch('/^[a-z0-9]+$/');

    expect([
        'users' => User::query()->count(),
        'teams' => Team::query()->count(),
        'businesses' => LocalBusiness::query()->count(),
        'integrations' => PartnerIntegration::query()->count(),
        'active_assignments' => PartnerPackageAssignment::query()->where('status', 'active')->count(),
        'onboarding_requests' => PartnerOnboardingRequest::query()->count(),
        'onboarding_tickets' => PartnerSupportTicketContext::query()->where('request_code', 'fizahub_onboarding')->count(),
        'support_tickets' => SupportTicket::query()->count(),
    ])->toBe($countsBeforeRepeat)
        ->and($countsBeforeRepeat)->toMatchArray([
            'users' => 1,
            'teams' => 1,
            'businesses' => 1,
            'integrations' => 1,
            'active_assignments' => 1,
            'onboarding_requests' => 1,
            'onboarding_tickets' => 1,
            'support_tickets' => 2,
        ]);
});
