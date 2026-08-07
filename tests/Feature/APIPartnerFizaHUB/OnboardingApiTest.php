<?php

use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
use Modules\APIPartnerFizaHUB\Services\FizaHubDefaultDataProvisioner;
use Modules\AppAdvancedCustomerCrm\Models\CustomerActivity;
use Modules\AppAffiliate\Models\AffiliateProfile;
use Modules\AppBookingPages\Models\BookingService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCustomers\Models\Customer;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppLandingPages\Support\LandingPageFactory;
use Modules\AppLoyaltyStampCards\Models\LoyaltyCard;
use Modules\AppQRCampaigns\Models\QrCampaign;

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
    createFizaHubDefaultDataTables();
}

function seedOnboardingPlan(): AdminPlan
{
    return AdminPlan::query()->create([
        'name' => 'MKT Free Da Nang',
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
        'marketing_goal_codes' => ['local_presence', 'qr_checkin'],
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

function unrelatedOnboardingPayload(string $key): array
{
    return validOnboardingPayload([
        'external_business_id' => 'fh-biz-'.$key,
        'external_user_id' => 'fh-user-'.$key,
        'owner' => [
            'name' => 'Unrelated Owner '.$key,
            'email' => 'unrelated-'.$key.'@example.com',
        ],
        'business' => [
            'name' => 'Unrelated Shop '.$key,
            'email' => 'shop-'.$key.'@example.com',
            'tax_code' => '0200'.str_pad((string) crc32($key), 6, '0', STR_PAD_LEFT),
            'business_license_number' => 'OTHER-'.$key,
        ],
    ]);
}

function generatedSlugConstraintException(string $table, string $slug): QueryException
{
    $previous = new PDOException(
        "SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint failed: {$table}.slug",
        23000
    );

    return new QueryException(
        'sqlite',
        "insert into {$table} (slug) values (?)",
        [$slug],
        $previous
    );
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);
    createOnboardingTestTables();
    seedOnboardingPlan();
});

afterEach(function (): void {
    dropFizaHubDefaultDataTables();
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
        ->assertJsonPath('data.current_step', 'awaiting_consultant')
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
        ->and(PartnerPackageAssignment::query()->count())->toBe(1)
        ->and(Customer::query()->count())->toBe(1)
        ->and(BookingService::query()->count())->toBe(1)
        ->and(LoyaltyCard::query()->count())->toBe(1)
        ->and(QrCampaign::query()->count())->toBe(5)
        ->and(QrCampaign::query()->whereNotNull('published_at')->count())->toBe(5)
        ->and(LandingPage::query()->count())->toBe(5)
        ->and(LandingPage::query()->where('status', 'published')->count())->toBe(5);

    $user = User::query()->firstOrFail();
    $business = LocalBusiness::query()->firstOrFail();
    $plan = AdminPlan::query()->where('slug', 'mlhub-free-da-nang')->firstOrFail();
    $integration = PartnerIntegration::query()->firstOrFail();
    $assignment = PartnerPackageAssignment::query()->firstOrFail();

    expect($user->email)->toBe('owner.a@example.com')
        ->and($user->timezone)->toBe('Asia/Ho_Chi_Minh')
        ->and($user->locale)->toBe('vi')
        ->and($user->plan_id)->toBe($plan->id)
        ->and($user->username)->toBe('ownera')
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

test('authenticated onboarding preserves a valid 255 character business name in every derived page and campaign', function (): void {
    $businessName = str_repeat('A', 255);

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload([
            'external_business_id' => 'fh-biz-long-name',
            'external_user_id' => 'fh-user-long-name',
            'owner' => ['email' => 'long-name@example.com'],
            'business' => ['name' => $businessName],
        ]),
        onboardingHeaders()
    )->assertCreated();

    $expectedNames = [
        'review' => 'Đánh giá trải nghiệm tại '.$businessName,
        'booking' => 'Đặt lịch hẹn với '.$businessName,
        'coupon' => 'Ưu đãi chào mừng 10% tại '.$businessName,
        'feedback' => 'Đánh giá sản phẩm và dịch vụ tại '.$businessName,
        'lead' => 'Đăng ký nhận tư vấn từ '.$businessName,
    ];

    expect(LocalBusiness::query()->sole()->name)->toBe($businessName);

    foreach ($expectedNames as $type => $expectedName) {
        $campaign = QrCampaign::query()->where('type', $type)->sole();
        $page = LandingPage::query()->where('campaign_id', $campaign->id)->sole();

        expect($campaign->name)->toBe($expectedName)
            ->and($page->title)->toBe($expectedName)
            ->and(strlen($campaign->slug))->toBeLessThanOrEqual(255)
            ->and(strlen($page->slug))->toBeLessThanOrEqual(255);
    }
});

test('same mapped business upsert updates permitted fields once and keeps a single onboarding ticket', function (): void {
    $requestId = (string) str()->uuid();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload(),
        onboardingHeaders(['X-Request-Id' => $requestId])
    )->assertCreated();

    $defaultDataCountsBeforeReplay = [
        'customers' => Customer::query()->count(),
        'booking_services' => BookingService::query()->count(),
        'loyalty_cards' => LoyaltyCard::query()->count(),
        'campaigns' => QrCampaign::query()->count(),
        'landing_pages' => LandingPage::query()->count(),
    ];
    $defaultDataIdsBeforeReplay = data_get(
        PartnerIntegration::query()->firstOrFail()->metadata,
        '_system.fizahub_default_data'
    );

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload([
            'owner' => ['name' => 'Nguyen Van B'],
            'business' => ['name' => 'Quan Com B'],
        ]),
        onboardingHeaders(['X-Request-Id' => $requestId])
    )
        ->assertOk()
        ->assertJsonPath('data.status', 'awaiting_consultant')
        ->assertJsonPath('data.already_registered', true)
        ->assertJsonPath('data.account_created', false)
        ->assertJsonPath('data.business_created', false)
        ->assertJsonPath('data.integration_created', false);

    expect(User::query()->count())->toBe(1)
        ->and(LocalBusiness::query()->count())->toBe(1)
        ->and(PartnerIntegration::query()->count())->toBe(1)
        ->and(PartnerOnboardingRequest::query()->count())->toBe(1)
        ->and(SupportTicket::query()->count())->toBe(1)
        ->and(PartnerPackageAssignment::query()->count())->toBe(1)
        ->and(Customer::query()->count())->toBe(1)
        ->and(BookingService::query()->count())->toBe(1)
        ->and(LoyaltyCard::query()->count())->toBe(1)
        ->and(QrCampaign::query()->count())->toBe(5)
        ->and(LandingPage::query()->count())->toBe(5)
        ->and([
            'customers' => Customer::query()->count(),
            'booking_services' => BookingService::query()->count(),
            'loyalty_cards' => LoyaltyCard::query()->count(),
            'campaigns' => QrCampaign::query()->count(),
            'landing_pages' => LandingPage::query()->count(),
        ])->toBe($defaultDataCountsBeforeReplay)
        ->and(data_get(PartnerIntegration::query()->firstOrFail()->metadata, '_system.fizahub_default_data'))
        ->toBe($defaultDataIdsBeforeReplay)
        ->and(User::query()->value('name'))->toBe('Nguyen Van B')
        ->and(LocalBusiness::query()->value('name'))->toBe('Quan Com B');
});

test('authenticated replay backfills a legacy integration without default-data metadata', function (): void {
    $payload = validOnboardingPayload();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )->assertCreated();

    LandingPage::query()->delete();
    QrCampaign::query()->delete();
    LoyaltyCard::query()->delete();
    BookingService::query()->delete();
    Customer::query()->delete();

    $integration = PartnerIntegration::query()->firstOrFail();
    $metadata = $integration->metadata;
    data_forget($metadata, '_system.fizahub_default_data');
    $metadata['legacy_marker'] = ['preserve' => true];
    $integration->forceFill(['metadata' => $metadata])->save();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )->assertOk()->assertJsonPath('data.already_registered', true);

    $integration = $integration->fresh();
    $state = data_get($integration->metadata, '_system.fizahub_default_data');

    expect(Customer::query()->count())->toBe(1)
        ->and(BookingService::query()->count())->toBe(1)
        ->and(LoyaltyCard::query()->count())->toBe(1)
        ->and(QrCampaign::query()->count())->toBe(5)
        ->and(LandingPage::query()->count())->toBe(5)
        ->and(data_get($integration->metadata, 'legacy_marker.preserve'))->toBeTrue()
        ->and($state['version'])->toBe('v1')
        ->and(array_keys((array) data_get($state, 'landing_page_ids')))->toBe(['review', 'booking', 'coupon', 'feedback', 'lead']);
});

test('authenticated replay upgrades v1 page metadata without overwriting extensions or rows', function (): void {
    $payload = validOnboardingPayload();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )->assertCreated();

    $integration = PartnerIntegration::query()->firstOrFail();
    $metadata = $integration->metadata;
    data_forget($metadata, '_system.fizahub_default_data.landing_page_ids');
    data_set($metadata, '_system.fizahub_default_data.extension', ['preserve' => 'v1']);
    $integration->forceFill(['metadata' => $metadata])->save();
    $campaignIds = QrCampaign::query()->orderBy('type')->pluck('id', 'type')->all();
    $pageIds = LandingPage::query()->orderBy('type')->pluck('id', 'type')->all();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )->assertOk();

    $state = data_get($integration->fresh()->metadata, '_system.fizahub_default_data');

    expect(QrCampaign::query()->orderBy('type')->pluck('id', 'type')->all())->toBe($campaignIds)
        ->and(LandingPage::query()->orderBy('type')->pluck('id', 'type')->all())->toBe($pageIds)
        ->and(data_get($state, 'landing_page_ids'))->toBe([
            'review' => $pageIds['review'],
            'booking' => $pageIds['booking'],
            'coupon' => $pageIds['coupon'],
            'feedback' => $pageIds['feedback'],
            'lead' => $pageIds['lead'],
        ])
        ->and(data_get($state, 'extension.preserve'))->toBe('v1');
});

test('authenticated replay repairs only a missing page and preserves nested user edits', function (): void {
    $payload = validOnboardingPayload();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )->assertCreated();

    $integration = PartnerIntegration::query()->firstOrFail();
    $customer = Customer::query()->sole();
    $service = BookingService::query()->sole();
    $card = LoyaltyCard::query()->sole();
    $review = QrCampaign::query()->where('type', 'review')->sole();
    $reviewPage = LandingPage::query()->where('type', 'review')->sole();
    $couponPage = LandingPage::query()->where('type', 'coupon')->sole();

    $customer->forceFill([
        'note' => 'Ghi chú do người dùng sửa',
        'metadata' => array_replace_recursive($customer->metadata ?? [], ['user' => ['segment' => 'vip']]),
    ])->save();
    $service->forceFill([
        'description' => 'Mô tả dịch vụ do người dùng sửa',
        'time_slots' => ['08:30', '13:30'],
    ])->save();
    $cardSettings = $card->settings;
    data_set($cardSettings, 'design.primary_color', '#123456');
    $card->forceFill(['reward_title' => 'Phần thưởng do người dùng sửa', 'settings' => $cardSettings])->save();
    $reviewSettings = $review->settings;
    data_set($reviewSettings, 'design.primary_color', '#654321');
    $review->forceFill(['settings' => $reviewSettings])->save();
    $reviewContent = $reviewPage->content;
    data_set($reviewContent, 'benefits.0', 'Nội dung do người dùng sửa');
    $reviewPageSettings = $reviewPage->settings;
    data_set($reviewPageSettings, 'design.primary_color', '#abcdef');
    $reviewPage->forceFill(['content' => $reviewContent, 'settings' => $reviewPageSettings])->save();

    $deletedCouponPageId = $couponPage->id;
    $couponPage->delete();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )->assertOk();

    $repairedCouponPage = LandingPage::query()->where('type', 'coupon')->sole();

    expect(Customer::query()->count())->toBe(1)
        ->and(BookingService::query()->count())->toBe(1)
        ->and(LoyaltyCard::query()->count())->toBe(1)
        ->and(QrCampaign::query()->count())->toBe(5)
        ->and(LandingPage::query()->count())->toBe(5)
        ->and($customer->fresh()->note)->toBe('Ghi chú do người dùng sửa')
        ->and(data_get($customer->fresh()->metadata, 'user.segment'))->toBe('vip')
        ->and($service->fresh()->description)->toBe('Mô tả dịch vụ do người dùng sửa')
        ->and($service->fresh()->time_slots)->toBe(['08:30', '13:30'])
        ->and($card->fresh()->reward_title)->toBe('Phần thưởng do người dùng sửa')
        ->and(data_get($card->fresh()->settings, 'design.primary_color'))->toBe('#123456')
        ->and(data_get($review->fresh()->settings, 'design.primary_color'))->toBe('#654321')
        ->and(data_get($reviewPage->fresh()->content, 'benefits.0'))->toBe('Nội dung do người dùng sửa')
        ->and(data_get($reviewPage->fresh()->settings, 'design.primary_color'))->toBe('#abcdef')
        ->and($repairedCouponPage->id)->not->toBe($deletedCouponPageId)
        ->and(data_get($repairedCouponPage->content, 'cta'))->toBe('Nhận ưu đãi')
        ->and(data_get($integration->fresh()->metadata, '_system.fizahub_default_data.landing_page_ids.coupon'))
        ->toBe($repairedCouponPage->id);
});

test('authenticated replay reattaches the recorded orphan page after only its campaign is deleted', function (): void {
    $payload = validOnboardingPayload();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )->assertCreated();

    $integration = PartnerIntegration::query()->firstOrFail();
    $coupon = QrCampaign::query()->where('type', 'coupon')->sole();
    $couponPage = LandingPage::query()->where('campaign_id', $coupon->id)->sole();
    $unrelatedPage = LandingPage::query()->create([
        'user_id' => $couponPage->user_id,
        'business_id' => $couponPage->business_id,
        'campaign_id' => null,
        'slug' => 'user-created-untracked-coupon-page',
        'title' => 'Trang người dùng tự tạo',
        'type' => 'coupon',
        'template' => 'coupon_offer',
        'status' => 'draft',
        'content' => ['cta' => 'Không được thay đổi'],
        'settings' => ['origin' => 'user'],
    ]);
    $editedContent = array_replace($couponPage->content, ['cta' => 'CTA ưu đãi do người dùng sửa']);
    $couponPage->forceFill(['title' => 'Trang ưu đãi do người dùng sửa', 'content' => $editedContent])->save();
    $oldCampaignId = $coupon->id;
    $coupon->delete();

    expect($couponPage->fresh()->campaign_id)->toBeNull();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )->assertOk();

    $replacement = QrCampaign::query()->where('type', 'coupon')->sole();
    $trackedLandingPageIds = data_get(
        $integration->fresh()->metadata,
        '_system.fizahub_default_data.landing_page_ids'
    );

    expect(QrCampaign::query()->count())->toBe(5)
        ->and(LandingPage::query()->count())->toBe(6)
        ->and($replacement->id)->not->toBe($oldCampaignId)
        ->and($couponPage->fresh()->campaign_id)->toBe($replacement->id)
        ->and($couponPage->fresh()->title)->toBe('Trang ưu đãi do người dùng sửa')
        ->and(data_get($couponPage->fresh()->content, 'cta'))->toBe('CTA ưu đãi do người dùng sửa')
        ->and($unrelatedPage->fresh()->campaign_id)->toBeNull()
        ->and($unrelatedPage->fresh()->title)->toBe('Trang người dùng tự tạo')
        ->and($unrelatedPage->fresh()->content)->toBe(['cta' => 'Không được thay đổi'])
        ->and($unrelatedPage->fresh()->settings)->toBe(['origin' => 'user'])
        ->and($trackedLandingPageIds)->toHaveCount(5)
        ->and($trackedLandingPageIds)->not->toContain($unrelatedPage->id)
        ->and(data_get($integration->fresh()->metadata, '_system.fizahub_default_data.landing_page_ids.coupon'))
        ->toBe($couponPage->id);
});

test('authenticated replay rejects unknown newer metadata without overwriting it', function (): void {
    $payload = validOnboardingPayload();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )->assertCreated();

    $integration = PartnerIntegration::query()->firstOrFail();
    $metadata = $integration->metadata;
    data_set($metadata, '_system.fizahub_default_data.version', 'v2');
    data_set($metadata, '_system.fizahub_default_data.future_extension', ['mode' => 'future']);
    $integration->forceFill(['metadata' => $metadata])->save();
    $counts = [Customer::query()->count(), BookingService::query()->count(), LoyaltyCard::query()->count(), QrCampaign::query()->count(), LandingPage::query()->count()];

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )
        ->assertConflict()
        ->assertJsonPath('error.code', 'integration_broken');

    expect($integration->fresh()->metadata)->toBe($metadata)
        ->and([Customer::query()->count(), BookingService::query()->count(), LoyaltyCard::query()->count(), QrCampaign::query()->count(), LandingPage::query()->count()])
        ->toBe($counts);
});

test('authenticated replay rejects mapped business and workspace ownership from another tenant', function (string $poisonedParent): void {
    $payload = validOnboardingPayload();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )->assertCreated();
    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        unrelatedOnboardingPayload('parent-'.$poisonedParent),
        onboardingHeaders()
    )->assertCreated();

    $target = PartnerIntegration::query()->where('external_business_id', 'fh-biz-001')->firstOrFail();
    $unrelated = PartnerIntegration::query()->where('external_business_id', 'fh-biz-parent-'.$poisonedParent)->firstOrFail();
    $targetMetadata = $target->metadata;

    $target->forceFill($poisonedParent === 'business'
        ? ['mlhub_business_id' => $unrelated->mlhub_business_id]
        : ['mlhub_workspace_id' => $unrelated->mlhub_workspace_id]
    )->save();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )
        ->assertConflict()
        ->assertJsonPath('error.code', 'integration_broken');

    expect($target->fresh()->metadata)->toBe($targetMetadata)
        ->and(PartnerIntegration::query()->where('external_business_id', $unrelated->external_business_id)->count())->toBe(1)
        ->and(User::query()->whereKey($unrelated->mlhub_user_id)->exists())->toBeTrue()
        ->and(LocalBusiness::query()->whereKey($unrelated->mlhub_business_id)->exists())->toBeTrue();
})->with(['business', 'workspace']);

test('authenticated replay rejects a tracked campaign id with the wrong type', function (): void {
    $payload = validOnboardingPayload();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )->assertCreated();

    $integration = PartnerIntegration::query()->firstOrFail();
    $metadata = $integration->metadata;
    data_set(
        $metadata,
        '_system.fizahub_default_data.campaign_ids.coupon',
        data_get($metadata, '_system.fizahub_default_data.campaign_ids.review')
    );
    $integration->forceFill(['metadata' => $metadata])->save();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )
        ->assertConflict()
        ->assertJsonPath('error.code', 'integration_broken');

    expect(QrCampaign::query()->count())->toBe(5)
        ->and(QrCampaign::query()->where('type', 'coupon')->count())->toBe(1)
        ->and($integration->fresh()->metadata)->toBe($metadata);
});

test('authenticated replay rejects a tracked loyalty card from the wrong workspace', function (): void {
    $payload = validOnboardingPayload();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )->assertCreated();
    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        unrelatedOnboardingPayload('loyalty-workspace'),
        onboardingHeaders()
    )->assertCreated();

    $target = PartnerIntegration::query()->where('external_business_id', 'fh-biz-001')->firstOrFail();
    $unrelated = PartnerIntegration::query()->where('external_business_id', 'fh-biz-loyalty-workspace')->firstOrFail();
    $card = LoyaltyCard::query()->whereKey(data_get($target->metadata, '_system.fizahub_default_data.loyalty_card_id'))->firstOrFail();
    $card->forceFill(['team_id' => $unrelated->mlhub_workspace_id])->save();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )
        ->assertConflict()
        ->assertJsonPath('error.code', 'integration_broken');

    expect($card->fresh()->team_id)->toBe($unrelated->mlhub_workspace_id)
        ->and(LoyaltyCard::query()->where('business_id', $target->mlhub_business_id)->count())->toBe(1)
        ->and(LoyaltyCard::query()->where('business_id', $unrelated->mlhub_business_id)->count())->toBe(1);
});

test('authenticated replay rejects a tracked landing page owned by another tenant', function (): void {
    $payload = validOnboardingPayload();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )->assertCreated();
    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        unrelatedOnboardingPayload('landing-owner'),
        onboardingHeaders()
    )->assertCreated();

    $target = PartnerIntegration::query()->where('external_business_id', 'fh-biz-001')->firstOrFail();
    $unrelated = PartnerIntegration::query()->where('external_business_id', 'fh-biz-landing-owner')->firstOrFail();
    $unrelatedPage = LandingPage::query()
        ->where('business_id', $unrelated->mlhub_business_id)
        ->where('type', 'review')
        ->sole();
    $metadata = $target->metadata;
    data_set($metadata, '_system.fizahub_default_data.landing_page_ids.review', $unrelatedPage->id);
    $target->forceFill(['metadata' => $metadata])->save();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )
        ->assertConflict()
        ->assertJsonPath('error.code', 'integration_broken');

    expect($unrelatedPage->fresh()->user_id)->toBe($unrelated->mlhub_user_id)
        ->and($unrelatedPage->fresh()->business_id)->toBe($unrelated->mlhub_business_id)
        ->and(LandingPage::query()->where('business_id', $target->mlhub_business_id)->count())->toBe(5)
        ->and(LandingPage::query()->where('business_id', $unrelated->mlhub_business_id)->count())->toBe(5);
});

test('authenticated replay rejects an attached landing page with the wrong expected type', function (): void {
    $payload = validOnboardingPayload();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )->assertCreated();

    $integration = PartnerIntegration::query()->firstOrFail();
    $metadata = $integration->metadata;
    data_forget($metadata, '_system.fizahub_default_data.landing_page_ids');
    $integration->forceFill(['metadata' => $metadata])->save();
    $couponPage = LandingPage::query()->where('type', 'coupon')->sole();
    $couponPage->forceFill(['type' => 'lead'])->save();

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        $payload,
        onboardingHeaders()
    )
        ->assertConflict()
        ->assertJsonPath('error.code', 'integration_broken');

    expect($couponPage->fresh()->type)->toBe('lead')
        ->and(LandingPage::query()->count())->toBe(5);
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
        ->assertOk()
        ->assertJsonPath('data.status', 'awaiting_consultant')
        ->assertJsonPath('data.already_registered', true);

    expect(User::query()->count())->toBe(1)
        ->and(LocalBusiness::query()->count())->toBe(1)
        ->and(PartnerIntegration::query()->count())->toBe(1)
        ->and(PartnerOnboardingRequest::query()->count())->toBe(1)
        ->and(LocalBusiness::query()->value('name'))->toBe('Updated Shop');
});

test('duplicate owner email returns conflict without provisioning any partner resources', function (): void {
    User::query()->create([
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

    $response->assertConflict()
        ->assertJsonPath('error.code', 'email_already_registered')
        ->assertJsonPath('error.details.next_action', 'use_existing_account_or_contact_support');

    expect(PartnerIntegration::query()->count())->toBe(0)
        ->and(User::query()->count())->toBe(1)
        ->and(LocalBusiness::query()->count())->toBe(0)
        ->and(Team::query()->count())->toBe(0)
        ->and(PartnerOnboardingRequest::query()->count())->toBe(0)
        ->and(SupportTicket::query()->count())->toBe(0);
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

    $freshUser = User::query()->where('email', 'fresh@example.com')->firstOrFail();

    expect(PartnerIntegration::query()->count())->toBe(2)
        ->and(Customer::query()->where('user_id', $freshUser->id)->count())->toBe(1)
        ->and(BookingService::query()->where('user_id', $freshUser->id)->count())->toBe(1)
        ->and(LoyaltyCard::query()->where('user_id', $freshUser->id)->count())->toBe(1)
        ->and(QrCampaign::query()->where('user_id', $freshUser->id)->count())->toBe(5)
        ->and(LandingPage::query()->where('user_id', $freshUser->id)->count())->toBe(5);
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
        ->assertJsonPath('data.current_step', 'awaiting_consultant')
        ->assertJsonPath('data.request_id', $requestId);

    $user = User::query()->firstOrFail();

    expect(User::query()->count())->toBe(1)
        ->and(SupportTicket::query()->count())->toBe(1)
        ->and($user->email_verified_at)->toBeNull();
});

test('late landing-page failure through authenticated onboarding rolls back upstream defaults and listener state', function (): void {
    $factory = new class extends LandingPageFactory
    {
        public int $calls = 0;

        /** @var array<string, int> */
        public array $observedBeforeFailure = [];

        public function syncFromCampaign(QrCampaign $campaign, array $overrides = []): LandingPage
        {
            $this->calls++;

            if ($campaign->type === 'lead') {
                $this->observedBeforeFailure = [
                    'users' => User::query()->count(),
                    'teams' => Team::query()->count(),
                    'team_memberships' => DB::table('team_user')->count(),
                    'businesses' => LocalBusiness::query()->count(),
                    'affiliate_profiles' => AffiliateProfile::query()->count(),
                    'integrations' => PartnerIntegration::query()->count(),
                    'package_assignments' => PartnerPackageAssignment::query()->count(),
                    'customers' => Customer::query()->count(),
                    'customer_activities' => CustomerActivity::query()->count(),
                    'booking_services' => BookingService::query()->count(),
                    'loyalty_cards' => LoyaltyCard::query()->count(),
                    'campaigns' => QrCampaign::query()->count(),
                    'landing_pages' => LandingPage::query()->count(),
                ];

                throw new RuntimeException('forced late landing-page failure');
            }

            return parent::syncFromCampaign($campaign, $overrides);
        }
    };
    app()->instance(LandingPageFactory::class, $factory);
    app()->forgetInstance(FizaHubDefaultDataProvisioner::class);

    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests',
        validOnboardingPayload([
            'external_business_id' => 'fh-biz-late-rollback',
            'external_user_id' => 'fh-user-late-rollback',
            'owner' => ['email' => 'late-rollback@example.com'],
        ]),
        onboardingHeaders()
    )
        ->assertStatus(500)
        ->assertJsonPath('error.code', 'partner_api_error');

    expect($factory->calls)->toBe(5)
        ->and($factory->observedBeforeFailure)->toBe([
            'users' => 1,
            'teams' => 1,
            'team_memberships' => 1,
            'businesses' => 1,
            'affiliate_profiles' => 1,
            'integrations' => 1,
            'package_assignments' => 1,
            'customers' => 1,
            'customer_activities' => 1,
            'booking_services' => 1,
            'loyalty_cards' => 1,
            'campaigns' => 5,
            'landing_pages' => 4,
        ])
        ->and(User::query()->count())->toBe(0)
        ->and(Team::query()->count())->toBe(0)
        ->and(DB::table('team_user')->count())->toBe(0)
        ->and(LocalBusiness::query()->count())->toBe(0)
        ->and(AffiliateProfile::query()->count())->toBe(0)
        ->and(PartnerIntegration::query()->count())->toBe(0)
        ->and(PartnerOnboardingRequest::query()->count())->toBe(0)
        ->and(SupportTicket::query()->count())->toBe(0)
        ->and(PartnerPackageAssignment::query()->count())->toBe(0)
        ->and(Customer::query()->count())->toBe(0)
        ->and(CustomerActivity::query()->count())->toBe(0)
        ->and(BookingService::query()->count())->toBe(0)
        ->and(LoyaltyCard::query()->count())->toBe(0)
        ->and(QrCampaign::query()->count())->toBe(0)
        ->and(LandingPage::query()->count())->toBe(0);
});

test('generated slug constraint races restart against a committed winner', function (string $target): void {
    $winnerUser = User::query()->create([
        'name' => 'Committed winner owner',
        'username' => 'committed-winner-'.$target,
        'email' => 'committed-winner-'.$target.'@example.com',
        'password' => Hash::make('password-password-password-password-password-password-1234'),
    ]);
    $winnerTeam = Team::query()->create([
        'name' => 'Committed winner team',
        'slug' => 'committed-winner-'.$target,
        'owner_user_id' => $winnerUser->id,
    ]);
    $winnerBusiness = LocalBusiness::query()->create([
        'user_id' => $winnerUser->id,
        'name' => 'Committed winner business',
        'type' => 'other',
    ]);
    $winnerCampaignId = $target === 'landing_page'
        ? DB::table('lb_campaigns')->insertGetId([
            'user_id' => $winnerUser->id,
            'business_id' => $winnerBusiness->id,
            'slug' => 'committed-winner-parent-campaign',
            'name' => 'Committed winner parent campaign',
            'type' => 'review',
            'status' => 'active',
            'settings' => json_encode([]),
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ])
        : null;
    $collisionRaised = false;
    $capturedSlug = null;
    $winnerInserted = false;
    $winnerId = null;
    $table = match ($target) {
        'campaign' => 'lb_campaigns',
        'landing_page' => 'lb_landing_pages',
        default => 'lb_loyalty_cards',
    };
    $raiseCollision = function (QrCampaign|LandingPage|LoyaltyCard $model) use (
        &$collisionRaised,
        &$capturedSlug,
        $table
    ): void {
        if ($collisionRaised) {
            return;
        }

        $collisionRaised = true;
        $capturedSlug = (string) $model->slug;

        throw generatedSlugConstraintException($table, $capturedSlug);
    };

    if ($target === 'campaign') {
        QrCampaign::creating($raiseCollision);
    } elseif ($target === 'landing_page') {
        LandingPage::creating($raiseCollision);
    } else {
        LoyaltyCard::creating($raiseCollision);
    }

    DB::connection()->getEventDispatcher()?->listen(
        TransactionRolledBack::class,
        function (TransactionRolledBack $event) use (
            &$collisionRaised,
            &$capturedSlug,
            &$winnerInserted,
            &$winnerId,
            $target,
            $winnerUser,
            $winnerTeam,
            $winnerBusiness,
            $winnerCampaignId
        ): void {
            if (! $collisionRaised || $winnerInserted || $capturedSlug === null
                || $event->connection !== DB::connection()) {
                return;
            }

            $winnerInserted = true;
            $winnerId = match ($target) {
                'campaign' => DB::table('lb_campaigns')->insertGetId([
                    'user_id' => $winnerUser->id,
                    'business_id' => $winnerBusiness->id,
                    'slug' => $capturedSlug,
                    'name' => 'Committed concurrent campaign',
                    'type' => 'review',
                    'status' => 'active',
                    'settings' => json_encode([]),
                    'published_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]),
                'landing_page' => DB::table('lb_landing_pages')->insertGetId([
                    'user_id' => $winnerUser->id,
                    'business_id' => $winnerBusiness->id,
                    'campaign_id' => $winnerCampaignId,
                    'slug' => $capturedSlug,
                    'title' => 'Committed concurrent landing page',
                    'type' => 'review',
                    'template' => 'review_booster',
                    'status' => 'published',
                    'content' => json_encode([]),
                    'settings' => json_encode([]),
                    'published_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]),
                default => DB::table('lb_loyalty_cards')->insertGetId([
                    'user_id' => $winnerUser->id,
                    'team_id' => $winnerTeam->id,
                    'business_id' => $winnerBusiness->id,
                    'slug' => $capturedSlug,
                    'name' => 'Committed concurrent loyalty card',
                    'reward_title' => 'Committed concurrent reward',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]),
            };
        }
    );

    try {
        $this->postJson(
            '/api/v1/partners/fizahub/onboarding-requests',
            validOnboardingPayload([
                'external_business_id' => 'fh-biz-slug-'.$target,
                'external_user_id' => 'fh-user-slug-'.$target,
                'owner' => ['email' => 'slug-'.$target.'@example.com'],
            ]),
            onboardingHeaders()
        )->assertCreated();

        $targetUser = User::query()->where('email', 'slug-'.$target.'@example.com')->sole();
        $generatedSlug = match ($target) {
            'campaign' => QrCampaign::query()->where('user_id', $targetUser->id)->orderBy('id')->value('slug'),
            'landing_page' => LandingPage::query()->where('user_id', $targetUser->id)->orderBy('id')->value('slug'),
            default => LoyaltyCard::query()->where('user_id', $targetUser->id)->sole()->slug,
        };

        expect($collisionRaised)->toBeTrue()
            ->and($winnerInserted)->toBeTrue()
            ->and($winnerId)->not->toBeNull()
            ->and(DB::table($table)->where('id', $winnerId)->value('slug'))->toBe($capturedSlug)
            ->and($generatedSlug)->toBe($capturedSlug.'-2')
            ->and(strlen((string) $generatedSlug))->toBeLessThanOrEqual(255)
            ->and(PartnerIntegration::query()->where('mlhub_user_id', $targetUser->id)->count())->toBe(1)
            ->and(Customer::query()->where('user_id', $targetUser->id)->count())->toBe(1)
            ->and(CustomerActivity::query()->where('owner_user_id', $targetUser->id)->count())->toBe(1)
            ->and(BookingService::query()->where('user_id', $targetUser->id)->count())->toBe(1)
            ->and(LoyaltyCard::query()->where('user_id', $targetUser->id)->count())->toBe(1)
            ->and(QrCampaign::query()->where('user_id', $targetUser->id)->count())->toBe(5)
            ->and(LandingPage::query()->where('user_id', $targetUser->id)->count())->toBe(5);
    } finally {
        if ($target === 'campaign') {
            QrCampaign::flushEventListeners();
        } elseif ($target === 'landing_page') {
            LandingPage::flushEventListeners();
        } else {
            LoyaltyCard::flushEventListeners();
        }
    }
})->with(['campaign', 'landing_page', 'loyalty_card']);

test('generated slug races stop after five whole transaction attempts without residue', function (): void {
    $attempts = 0;

    QrCampaign::creating(function (QrCampaign $campaign) use (&$attempts): void {
        $attempts++;

        throw generatedSlugConstraintException('lb_campaigns', (string) $campaign->slug);
    });

    try {
        $this->postJson(
            '/api/v1/partners/fizahub/onboarding-requests',
            validOnboardingPayload([
                'external_business_id' => 'fh-biz-persistent-slug-race',
                'external_user_id' => 'fh-user-persistent-slug-race',
                'owner' => ['email' => 'persistent-slug-race@example.com'],
            ]),
            onboardingHeaders()
        )
            ->assertStatus(500)
            ->assertJsonPath('error.code', 'partner_api_error');

        expect($attempts)->toBe(5)
            ->and(User::query()->count())->toBe(0)
            ->and(Team::query()->count())->toBe(0)
            ->and(DB::table('team_user')->count())->toBe(0)
            ->and(LocalBusiness::query()->count())->toBe(0)
            ->and(AffiliateProfile::query()->count())->toBe(0)
            ->and(PartnerIntegration::query()->count())->toBe(0)
            ->and(PartnerPackageAssignment::query()->count())->toBe(0)
            ->and(Customer::query()->count())->toBe(0)
            ->and(CustomerActivity::query()->count())->toBe(0)
            ->and(BookingService::query()->count())->toBe(0)
            ->and(LoyaltyCard::query()->count())->toBe(0)
            ->and(QrCampaign::query()->count())->toBe(0)
            ->and(LandingPage::query()->count())->toBe(0);
    } finally {
        QrCampaign::flushEventListeners();
    }
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

test('removed cancel onboarding route remains unavailable after creating a request', function (): void {
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
    )->assertNotFound()->assertJsonPath('error.code', 'route_not_found');

    // Same request_id, cancelled again — must stay a clean 200/cancelled, not 422/500.
    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests/'.$requestId.'/cancel',
        ['reason' => 'Second cancel attempt'],
        onboardingHeaders()
    )->assertNotFound()->assertJsonPath('error.code', 'route_not_found');
});

test('removed cancel route returns the standard route_not_found error', function (): void {
    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests/'.((string) str()->uuid()).'/cancel',
        ['reason' => 'Does not exist'],
        onboardingHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'route_not_found');
});

test('removed confirm route returns the standard route_not_found error', function (): void {
    $this->postJson(
        '/api/v1/partners/fizahub/onboarding-requests/'.((string) str()->uuid()).'/confirm',
        ['note' => 'Does not exist'],
        onboardingHeaders()
    )
        ->assertNotFound()
        ->assertJsonPath('error.code', 'route_not_found');
});
