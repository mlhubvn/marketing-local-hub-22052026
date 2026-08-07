# FizaHUB Default Data Provisioning Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make a successful FizaHUB onboarding request synchronously provision a complete, published Vietnamese default data set before the new household-business user logs in.

**Architecture:** Add a FizaHUB-only `FizaHubDefaultDataProvisioner` and invoke it from both new-registration and existing-registration paths inside `OnboardingService`'s transaction. Persist generated entity IDs in `PartnerIntegration.metadata` so API replay can repair missing records without duplicating or overwriting user-edited content.

**Tech Stack:** PHP 8.3, Laravel 13, Eloquent, SQLite feature tests, Pest 4, Laravel Livewire domain models, `LandingPageFactory`, Laravel Pint.

## Global Constraints

- This is a privileged FizaHUB onboarding behavior inside `Modules\APIPartnerFizaHUB`, not a generic starter-kit feature or user-facing toggle.
- Provision defaults during the initial `POST /api/v1/partners/fizahub/onboarding-requests` transaction; do not wait for any Admin status.
- A successful response guarantees one first customer, one booking service, one loyalty card, five campaigns, and five published landing pages.
- Do not dispatch a background provisioning job.
- Do not apply interactive `PlanLimitGuard` checks to FizaHUB-provisioned defaults.
- Store stable generated IDs under `PartnerIntegration.metadata._system.fizahub_default_data` with version `v1`.
- Replays repair stale or missing generated IDs, preserve existing user edits, and never create duplicates.
- Any first-registration provisioning failure rolls back the complete onboarding transaction.
- Use the exact Vietnamese names, messages, 10% coupon, and 10% loyalty reward from the approved design spec.
- Do not change FizaHUB authentication, request validation, public statuses, support-ticket behavior, package assignment, webhook behavior, or CRM readiness rules.
- Implement production behavior with red-green-refactor; observe each test fail for the intended missing behavior before editing production code.

## File Map

- Create `modules/APIPartnerFizaHUB/Services/FizaHubDefaultDataProvisioner.php`: own all FizaHUB-only default entity reconciliation and integration metadata persistence.
- Modify `modules/APIPartnerFizaHUB/Services/OnboardingService.php`: inject and invoke the provisioner in both new and existing registration paths.
- Modify `tests/Feature/APIPartnerFizaHUB/FizaHubTestHelpers.php`: add exact lightweight schemas and cleanup helpers for generated portal data.
- Create `tests/Feature/APIPartnerFizaHUB/FizaHubDefaultDataProvisionerTest.php`: verify copy, URLs, publication, stable IDs, repair, and edit preservation directly against the provisioner.
- Modify `tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php`: verify API-level creation, `needs_review` independence, replay idempotency, and transaction rollback.
- Modify `tests/Feature/APIPartnerFizaHUB/OnboardingUiContractTest.php`: boot and clean the new required tables for existing UI contract coverage.
- Modify `tests/Feature/APIPartnerFizaHUB/RootCauseInvestigationTest.php`: boot and clean the new required tables for production-shaped onboarding regression coverage.
- Modify `tests/Feature/APIPartnerFizaHUB/FullUiHappyPathTest.php`: use the shared default-data schema while retaining its test-only campaign `objective` column and response tables.
- Modify `tests/Feature/APIPartnerFizaHUB/PartnerCrossEndpointTest.php`: replace its incomplete campaign fixture with the shared default-data schema.
- Modify `tests/Feature/APIPartnerFizaHUB/OnboardingAdminTransitionTest.php`: prove Admin transitions are data-neutral and user deletion removes generated data.

---

### Task 1: Provisioner contract and FizaHUB default-data service

**Files:**
- Modify: `tests/Feature/APIPartnerFizaHUB/FizaHubTestHelpers.php`
- Create: `tests/Feature/APIPartnerFizaHUB/FizaHubDefaultDataProvisionerTest.php`
- Create: `modules/APIPartnerFizaHUB/Services/FizaHubDefaultDataProvisioner.php`

**Interfaces:**
- Consumes: `PartnerIntegration`, `User`, `Team`, `LocalBusiness`, `LandingPageFactory`, `PageTemplateCatalog`.
- Produces: `public function provision(PartnerIntegration $integration, User $user, Team $team, LocalBusiness $business): void`.
- Persists: `metadata._system.fizahub_default_data = {version, customer_id, booking_service_id, loyalty_card_id, campaign_ids}`.

- [ ] **Step 1: Add reusable test schemas for the exact generated entity columns**

Append `createFizaHubDefaultDataTables()` and `dropFizaHubDefaultDataTables()` to `FizaHubTestHelpers.php`. Create/drop tables in dependency-safe order. The create helper must define:

```php
function createFizaHubDefaultDataTables(): void
{
    dropFizaHubDefaultDataTables();

    Schema::create('lb_customers', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
        $table->string('name');
        $table->string('phone')->nullable();
        $table->string('email')->nullable();
        $table->json('tags')->nullable();
        $table->text('note')->nullable();
        $table->json('metadata')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_campaigns', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('business_id')->constrained('lb_businesses')->cascadeOnDelete();
        $table->string('slug')->unique();
        $table->string('name');
        $table->string('type', 40);
        $table->string('status', 30)->default('active');
        $table->text('destination_url')->nullable();
        $table->json('settings')->nullable();
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_booking_services', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('business_id')->constrained('lb_businesses')->cascadeOnDelete();
        $table->string('name');
        $table->unsignedSmallInteger('duration_minutes')->default(60);
        $table->decimal('price', 10, 2)->nullable();
        $table->text('description')->nullable();
        $table->json('available_days')->nullable();
        $table->json('time_slots')->nullable();
        $table->unsignedSmallInteger('max_bookings_per_slot')->default(1);
        $table->boolean('use_business_hours')->default(true);
        $table->unsignedSmallInteger('slot_interval')->default(30);
        $table->unsignedSmallInteger('buffer_before')->default(0);
        $table->unsignedSmallInteger('buffer_after')->default(0);
        $table->json('service_hours')->nullable();
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    Schema::create('lb_landing_pages', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('business_id')->nullable()->constrained('lb_businesses')->nullOnDelete();
        $table->foreignId('campaign_id')->nullable()->constrained('lb_campaigns')->nullOnDelete();
        $table->string('slug')->unique();
        $table->string('title');
        $table->string('type', 40)->default('lead');
        $table->string('template', 80)->default('local_campaign');
        $table->string('status', 30)->default('published');
        $table->json('content')->nullable();
        $table->json('settings')->nullable();
        $table->unsignedInteger('visits_count')->default(0);
        $table->unsignedInteger('conversions_count')->default(0);
        $table->timestamp('published_at')->nullable();
        $table->timestamps();
    });

    Schema::create('lb_loyalty_cards', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        $table->foreignId('team_id')->nullable()->constrained('teams')->nullOnDelete();
        $table->foreignId('business_id')->constrained('lb_businesses')->cascadeOnDelete();
        $table->string('slug')->unique();
        $table->string('name');
        $table->unsignedInteger('required_stamps')->default(10);
        $table->string('stamp_method')->default('qr_scan');
        $table->string('customer_identifier')->default('phone');
        $table->string('reward_title');
        $table->string('reward_type')->default('free_item');
        $table->string('reward_value')->nullable();
        $table->unsignedInteger('expiry_days')->nullable();
        $table->unsignedInteger('stamp_cooldown_minutes')->default(1440);
        $table->unsignedInteger('max_stamps_per_day')->default(1);
        $table->json('settings')->nullable();
        $table->string('status')->default('active');
        $table->timestamps();
    });
}

function dropFizaHubDefaultDataTables(): void
{
    Schema::dropIfExists('lb_landing_pages');
    Schema::dropIfExists('lb_loyalty_cards');
    Schema::dropIfExists('lb_booking_services');
    Schema::dropIfExists('lb_campaigns');
    Schema::dropIfExists('lb_customers');
}
```

- [ ] **Step 2: Write failing direct provisioner tests**

Create `FizaHubDefaultDataProvisionerTest.php` using `bootProductionLikeSchema()` and `createFizaHubDefaultDataTables()`. Use this exact setup and owner helper before adding the tests:

```php
function seedDefaultDataOwner(): array
{
    $user = User::query()->create([
        'name' => 'Chủ cơ sở',
        'username' => 'defaultdataowner',
        'email' => 'owner@example.com',
        'password' => 'password-password-password-password-password-password-1234',
    ]);
    $team = Team::query()->create([
        'name' => 'Cửa hàng Ánh Dương Team',
        'slug' => 'cua-hang-anh-duong-team',
        'owner_user_id' => $user->id,
    ]);
    $business = LocalBusiness::query()->create([
        'user_id' => $user->id,
        'name' => 'Cửa hàng Ánh Dương',
        'type' => 'other',
        'phone' => '0901234567',
        'email' => null,
        'address' => 'Đà Nẵng',
    ]);
    $integration = PartnerIntegration::query()->create([
        'partner_code' => 'fizahub',
        'external_business_id' => 'default-data-business',
        'external_user_id' => 'default-data-owner',
        'mlhub_user_id' => $user->id,
        'mlhub_workspace_id' => $team->id,
        'mlhub_business_id' => $business->id,
        'package_code' => 'free',
        'status' => 'active',
        'metadata' => ['tax_code' => '0400123456'],
    ]);

    return compact('integration', 'user', 'team', 'business');
}

beforeEach(function (): void {
    bootProductionLikeSchema();
    createFizaHubDefaultDataTables();
});

afterEach(function (): void {
    dropFizaHubDefaultDataTables();
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
```

Import `Schema`, `User`, `Team`, `PartnerIntegration`, `FizaHubDefaultDataProvisioner`, `LocalBusiness`, `BookingService`, `Customer`, `LandingPage`, `LoyaltyCard`, and `QrCampaign`. Add these tests:

```php
test('FizaHUB provisioner creates the complete published Vietnamese default data contract', function (): void {
    ['integration' => $integration, 'user' => $user, 'team' => $team, 'business' => $business] = seedDefaultDataOwner();

    app(FizaHubDefaultDataProvisioner::class)->provision($integration, $user, $team, $business);

    expect(Customer::query()->count())->toBe(1)
        ->and(BookingService::query()->count())->toBe(1)
        ->and(LoyaltyCard::query()->count())->toBe(1)
        ->and(QrCampaign::query()->count())->toBe(5)
        ->and(LandingPage::query()->count())->toBe(5)
        ->and(QrCampaign::query()->whereNotNull('published_at')->count())->toBe(5)
        ->and(LandingPage::query()->where('status', 'published')->count())->toBe(5);

    $customer = Customer::query()->firstOrFail();
    $review = QrCampaign::query()->where('type', 'review')->firstOrFail();
    $coupon = QrCampaign::query()->where('type', 'coupon')->firstOrFail();
    $feedback = QrCampaign::query()->where('type', 'feedback')->firstOrFail();
    $lead = QrCampaign::query()->where('type', 'lead')->firstOrFail();
    $service = BookingService::query()->firstOrFail();
    $card = LoyaltyCard::query()->firstOrFail();

    expect($customer->name)->toBe('Cửa hàng Ánh Dương')
        ->and($customer->email)->toBe('owner@example.com')
        ->and($review->settings['google_review_url'])->toBe('https://www.google.com/maps/search/Cua+hang+Anh+Duong')
        ->and($review->settings['facebook_review_url'])->toBe('https://www.facebook.com/cua-hang-anh-duong')
        ->and($coupon->settings['discount_type'])->toBe('percentage')
        ->and((string) $coupon->settings['discount_value'])->toBe('10')
        ->and($coupon->settings['coupon_code'])->toBe('CHAO10')
        ->and($feedback->settings['rating_required'])->toBeTrue()
        ->and($feedback->settings['contact_required'])->toBeFalse()
        ->and($lead->settings['headline'])->toBe('Để lại thông tin để được tư vấn sản phẩm và dịch vụ phù hợp')
        ->and($service->name)->toBe('Tư vấn sản phẩm và dịch vụ')
        ->and($service->duration_minutes)->toBe(60)
        ->and($card->required_stamps)->toBe(10)
        ->and($card->reward_value)->toBe('Giảm 10%');

    $state = data_get($integration->fresh()->metadata, '_system.fizahub_default_data');
    expect($state['version'])->toBe('v1')
        ->and($state['customer_id'])->toBe($customer->id)
        ->and($state['booking_service_id'])->toBe($service->id)
        ->and($state['loyalty_card_id'])->toBe($card->id)
        ->and(array_keys($state['campaign_ids']))->toBe(['review', 'booking', 'coupon', 'feedback', 'lead']);
});

test('FizaHUB provisioner reuses stable ids preserves edits and repairs only deleted generated data', function (): void {
    $seed = seedDefaultDataOwner();
    $service = app(FizaHubDefaultDataProvisioner::class);
    $service->provision($seed['integration'], $seed['user'], $seed['team'], $seed['business']);

    $review = QrCampaign::query()->where('type', 'review')->firstOrFail();
    $reviewPage = LandingPage::query()->where('campaign_id', $review->id)->firstOrFail();
    $review->forceFill(['name' => 'Tên người dùng đã sửa'])->save();
    $reviewPage->forceFill(['title' => 'Trang người dùng đã sửa'])->save();

    $coupon = QrCampaign::query()->where('type', 'coupon')->firstOrFail();
    LandingPage::query()->where('campaign_id', $coupon->id)->delete();
    $coupon->delete();

    $service->provision($seed['integration']->fresh(), $seed['user'], $seed['team'], $seed['business']);

    expect(QrCampaign::query()->count())->toBe(5)
        ->and(LandingPage::query()->count())->toBe(5)
        ->and(Customer::query()->count())->toBe(1)
        ->and(BookingService::query()->count())->toBe(1)
        ->and(LoyaltyCard::query()->count())->toBe(1)
        ->and($review->fresh()->name)->toBe('Tên người dùng đã sửa')
        ->and($reviewPage->fresh()->title)->toBe('Trang người dùng đã sửa')
        ->and(data_get($seed['integration']->fresh()->metadata, '_system.fizahub_default_data.campaign_ids.coupon'))
        ->not->toBe($coupon->id);
});
```

The business email is intentionally `null`, so the first test proves owner-email fallback.

- [ ] **Step 3: Run the tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/FizaHubDefaultDataProvisionerTest.php
```

Expected: FAIL because `Modules\APIPartnerFizaHUB\Services\FizaHubDefaultDataProvisioner` does not exist.

- [ ] **Step 4: Implement `FizaHubDefaultDataProvisioner`**

Create the service with this public entry point and state flow:

```php
class FizaHubDefaultDataProvisioner
{
    private const VERSION = 'v1';
    private const STATE_PATH = '_system.fizahub_default_data';
    private const CAMPAIGN_TYPES = ['review', 'booking', 'coupon', 'feedback', 'lead'];

    public function __construct(private readonly LandingPageFactory $landingPages) {}

    public function provision(
        PartnerIntegration $integration,
        User $user,
        Team $team,
        LocalBusiness $business
    ): void {
        $state = (array) data_get($integration->metadata, self::STATE_PATH, []);
        $customer = $this->customer($state['customer_id'] ?? null, $user, $business);
        $bookingService = $this->bookingService($state['booking_service_id'] ?? null, $user, $business);
        $loyaltyCard = $this->loyaltyCard($state['loyalty_card_id'] ?? null, $user, $team, $business);

        $campaignIds = [];
        foreach (self::CAMPAIGN_TYPES as $type) {
            $campaign = $this->campaign(
                $type,
                data_get($state, 'campaign_ids.'.$type),
                $user,
                $business
            );
            $campaignIds[$type] = $campaign->id;

            if (! LandingPage::query()->where('campaign_id', $campaign->id)->exists()) {
                $this->landingPages->syncFromCampaign($campaign);
            }
        }

        $metadata = (array) ($integration->metadata ?? []);
        data_set($metadata, self::STATE_PATH, [
            'version' => self::VERSION,
            'customer_id' => $customer->id,
            'booking_service_id' => $bookingService->id,
            'loyalty_card_id' => $loyaltyCard->id,
            'campaign_ids' => $campaignIds,
        ]);
        $integration->forceFill(['metadata' => $metadata])->save();
    }
}
```

Implement ownership validation for stored IDs with `whereKey()`, `where('user_id', $user->id)`, `where('business_id', $business->id)`, and campaign `where('type', $type)`. Create only when that scoped lookup returns `null`.

Use these exact entity attributes:

```php
$customer = Customer::query()->create([
    'user_id' => $user->id,
    'business_id' => $business->id,
    'name' => $business->name,
    'phone' => $business->phone,
    'email' => $business->email ?: $user->email,
    'note' => 'Khách hàng đầu tiên được tạo tự động từ thông tin cơ sở kinh doanh.',
    'metadata' => ['source' => 'fizahub_onboarding'],
]);

$bookingService = BookingService::query()->create([
    'user_id' => $user->id,
    'business_id' => $business->id,
    'name' => 'Tư vấn sản phẩm và dịch vụ',
    'duration_minutes' => 60,
    'price' => null,
    'description' => 'Đặt lịch để được tư vấn về sản phẩm, dịch vụ và giải pháp phù hợp với nhu cầu của bạn.',
    'available_days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'],
    'time_slots' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
    'use_business_hours' => true,
    'slot_interval' => 30,
    'buffer_before' => 0,
    'buffer_after' => 0,
    'service_hours' => null,
    'is_active' => true,
]);

$loyaltyCard = LoyaltyCard::query()->create([
    'user_id' => $user->id,
    'team_id' => $team->id,
    'business_id' => $business->id,
    'slug' => $this->uniqueSlug(LoyaltyCard::query(), 'Thẻ tích điểm khách hàng thân thiết', 'loyalty-card'),
    'name' => 'Thẻ tích điểm khách hàng thân thiết',
    'required_stamps' => 10,
    'stamp_method' => 'qr_scan',
    'customer_identifier' => 'phone',
    'reward_title' => 'Ưu đãi 10% cho lần sử dụng tiếp theo',
    'reward_type' => 'discount',
    'reward_value' => 'Giảm 10%',
    'expiry_days' => 30,
    'stamp_cooldown_minutes' => 1440,
    'max_stamps_per_day' => 1,
    'settings' => $this->defaultDesignSettingsForType('loyalty'),
    'status' => 'active',
]);
```

The campaign definition method returns these exact names and type-specific settings, merged with `defaultDesignSettingsForType($type)`:

```php
private function campaignDefinition(string $type, LocalBusiness $business): array
{
    $name = (string) $business->name;

    return match ($type) {
        'review' => [
            'name' => "Đánh giá trải nghiệm tại {$name}",
            'settings' => [
                'google_review_url' => 'https://www.google.com/maps/search/'.$this->googleName($name),
                'facebook_review_url' => 'https://www.facebook.com/'.Str::slug($name),
                'preferred_destination' => 'google',
                'positive_threshold' => 4,
                'thank_you_message' => 'Cảm ơn bạn đã tin tưởng và sử dụng sản phẩm, dịch vụ của chúng tôi. Đánh giá của bạn giúp chúng tôi phục vụ tốt hơn.',
                'negative_feedback_message' => 'Chúng tôi rất tiếc khi trải nghiệm của bạn chưa như mong đợi. Vui lòng chia sẻ thêm để chúng tôi có thể cải thiện.',
            ],
        ],
        'booking' => [
            'name' => "Đặt lịch hẹn với {$name}",
            'settings' => ['headline' => "Đặt lịch tư vấn cùng {$name}"],
        ],
        'coupon' => [
            'name' => "Ưu đãi chào mừng 10% tại {$name}",
            'settings' => [
                'discount_type' => 'percentage',
                'discount_value' => '10',
                'coupon_code' => 'CHAO10',
                'usage_limit' => null,
                'expiry_date' => null,
                'terms' => 'Giảm 10% cho một lần sử dụng sản phẩm hoặc dịch vụ. Không áp dụng đồng thời với chương trình ưu đãi khác.',
                'status' => 'active',
            ],
        ],
        'feedback' => [
            'name' => "Đánh giá sản phẩm và dịch vụ tại {$name}",
            'settings' => [
                'headline' => 'Bạn đánh giá thế nào về sản phẩm và dịch vụ của chúng tôi?',
                'thank_you_message' => 'Cảm ơn bạn đã dành thời gian đánh giá. Ý kiến của bạn giúp chúng tôi cải thiện chất lượng phục vụ.',
                'rating_required' => true,
                'contact_required' => false,
            ],
        ],
        'lead' => [
            'name' => "Đăng ký nhận tư vấn từ {$name}",
            'settings' => ['headline' => 'Để lại thông tin để được tư vấn sản phẩm và dịch vụ phù hợp'],
        ],
    };
}
```

For every new campaign, create `user_id`, `business_id`, `type`, a globally unique slug, `status = active`, merged settings, and `published_at = now()`. `defaultDesignSettingsForType()` returns `create_public_page = true`, `generate_qr_code = true`, `landing_template`, and `design` from `PageTemplateCatalog`. `googleName()` applies `Str::ascii()`, `Str::squish()`, then replaces spaces with `+`. `uniqueSlug()` checks the model query's `slug` column and appends `-2`, `-3`, and so on.

- [ ] **Step 5: Run direct tests and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/FizaHubDefaultDataProvisionerTest.php
vendor/bin/pint --test modules/APIPartnerFizaHUB/Services/FizaHubDefaultDataProvisioner.php tests/Feature/APIPartnerFizaHUB/FizaHubDefaultDataProvisionerTest.php tests/Feature/APIPartnerFizaHUB/FizaHubTestHelpers.php
```

Expected: both provisioner tests PASS and Pint reports no style changes required.

- [ ] **Step 6: Commit the provisioner**

```powershell
git add modules/APIPartnerFizaHUB/Services/FizaHubDefaultDataProvisioner.php tests/Feature/APIPartnerFizaHUB/FizaHubDefaultDataProvisionerTest.php tests/Feature/APIPartnerFizaHUB/FizaHubTestHelpers.php
git commit -m "feat: add FizaHUB default data provisioner"
```

---

### Task 2: Wire synchronous provisioning into both onboarding paths

**Files:**
- Modify: `modules/APIPartnerFizaHUB/Services/OnboardingService.php:17-28`
- Modify: `modules/APIPartnerFizaHUB/Services/OnboardingService.php:251-322`
- Modify: `modules/APIPartnerFizaHUB/Services/OnboardingService.php:325-455`
- Modify: `tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php`

**Interfaces:**
- Consumes: `FizaHubDefaultDataProvisioner::provision(PartnerIntegration, User, Team, LocalBusiness): void` from Task 1.
- Produces: API success only after all defaults exist; existing registrations reconcile defaults without changing creation flags.

- [ ] **Step 1: Boot the generated-data tables in `OnboardingApiTest`**

Call `createFizaHubDefaultDataTables()` at the end of `createOnboardingTestTables()`. Call `dropFizaHubDefaultDataTables()` first in `afterEach()`. Import `BookingService`, `Customer`, `LandingPage`, `LoyaltyCard`, and `QrCampaign`.

- [ ] **Step 2: Write failing API creation and status-independence assertions**

Extend `onboarding always provisions a free account awaiting consultant with requested package stored separately`:

```php
expect(Customer::query()->count())->toBe(1)
    ->and(BookingService::query()->count())->toBe(1)
    ->and(LoyaltyCard::query()->count())->toBe(1)
    ->and(QrCampaign::query()->count())->toBe(5)
    ->and(QrCampaign::query()->whereNotNull('published_at')->count())->toBe(5)
    ->and(LandingPage::query()->count())->toBe(5)
    ->and(LandingPage::query()->where('status', 'published')->count())->toBe(5);
```

Extend `duplicate tax code on another integration still provisions with needs_review` by resolving the fresh user and asserting exactly five campaigns, five landing pages, one customer, one booking service, and one loyalty card scoped to that user. This proves `needs_review` is only an Admin workflow state.

- [ ] **Step 3: Write a failing replay test for stable counts**

In `same mapped business upsert updates permitted fields once and keeps a single onboarding ticket`, capture generated row counts and the integration metadata ID map before the second request, then assert after the request:

```php
expect(Customer::query()->count())->toBe(1)
    ->and(BookingService::query()->count())->toBe(1)
    ->and(LoyaltyCard::query()->count())->toBe(1)
    ->and(QrCampaign::query()->count())->toBe(5)
    ->and(LandingPage::query()->count())->toBe(5)
    ->and(data_get(PartnerIntegration::query()->firstOrFail()->metadata, '_system.fizahub_default_data'))
    ->toBe($defaultDataIdsBeforeReplay);
```

- [ ] **Step 4: Run API tests and verify RED**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php --filter="onboarding always provisions|duplicate tax code|same mapped business"
```

Expected: FAIL with zero generated records because `OnboardingService` does not invoke the provisioner yet.

- [ ] **Step 5: Inject and call the provisioner**

Add `protected FizaHubDefaultDataProvisioner $defaultData` to the constructor.

In `reuseExistingRegistration()`, replace the boolean team existence query with a locked `Team` model lookup. Keep the existing `integration_broken` guard, then run:

```php
$this->updateExistingProfile($user, $business, $acceptedPayload);
$this->defaultData->provision($integration, $user, $team, $business);
```

In `provisionNewRegistration()`, call the provisioner immediately after `assignEffectivePackage()` and before creating `PartnerOnboardingRequest`:

```php
$this->packages->assignEffectivePackage(
    $integration,
    $effectivePackageCode,
    null,
    'Initial Free package on FizaHUB provisioning.'
);

$this->defaultData->provision($integration, $user, $team, $business);
```

Do not add conditionals for onboarding status and do not catch provisioner exceptions inside the transaction.

- [ ] **Step 6: Write and run the rollback regression**

Add this test and import `FizaHubDefaultDataProvisioner` plus `OnboardingService`:

```php
test('default-data failure rolls back the complete first onboarding transaction', function (): void {
    $provisioner = Mockery::mock(FizaHubDefaultDataProvisioner::class);
    $provisioner->shouldReceive('provision')
        ->once()
        ->andThrow(new \RuntimeException('forced default-data failure'));
    app()->instance(FizaHubDefaultDataProvisioner::class, $provisioner);

    expect(fn () => app(OnboardingService::class)->upsert(
        validOnboardingPayload(),
        (string) str()->uuid()
    ))->toThrow(\RuntimeException::class, 'forced default-data failure');

    expect(User::query()->count())->toBe(0)
        ->and(Team::query()->count())->toBe(0)
        ->and(LocalBusiness::query()->count())->toBe(0)
        ->and(PartnerIntegration::query()->count())->toBe(0)
        ->and(PartnerOnboardingRequest::query()->count())->toBe(0)
        ->and(SupportTicket::query()->count())->toBe(0)
        ->and(PartnerPackageAssignment::query()->count())->toBe(0)
        ->and(Customer::query()->count())->toBe(0)
        ->and(BookingService::query()->count())->toBe(0)
        ->and(LoyaltyCard::query()->count())->toBe(0)
        ->and(QrCampaign::query()->count())->toBe(0)
        ->and(LandingPage::query()->count())->toBe(0);
});
```

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php --filter="default-data failure"
```

Expected: PASS, proving the existing `DB::transaction()` rolls back every created row.

- [ ] **Step 7: Run the focused onboarding suite and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php
vendor/bin/pint --test modules/APIPartnerFizaHUB/Services/OnboardingService.php tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php
```

Expected: all `OnboardingApiTest` tests PASS and Pint is clean.

- [ ] **Step 8: Commit onboarding integration**

```powershell
git add modules/APIPartnerFizaHUB/Services/OnboardingService.php tests/Feature/APIPartnerFizaHUB/OnboardingApiTest.php
git commit -m "feat: provision FizaHUB defaults during onboarding"
```

---

### Task 3: Keep production-shaped FizaHUB suites aligned with the new required data graph

**Files:**
- Modify: `tests/Feature/APIPartnerFizaHUB/OnboardingUiContractTest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/RootCauseInvestigationTest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/FullUiHappyPathTest.php`
- Modify: `tests/Feature/APIPartnerFizaHUB/PartnerCrossEndpointTest.php`

**Interfaces:**
- Consumes: `createFizaHubDefaultDataTables()` and `dropFizaHubDefaultDataTables()` from Task 1.
- Produces: all existing endpoint and production-incident tests run against the now-required onboarding data tables.

- [ ] **Step 1: Run the affected suites and capture the expected fixture failures**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/OnboardingUiContractTest.php tests/Feature/APIPartnerFizaHUB/RootCauseInvestigationTest.php tests/Feature/APIPartnerFizaHUB/FullUiHappyPathTest.php tests/Feature/APIPartnerFizaHUB/PartnerCrossEndpointTest.php
```

Expected: FAIL where successful onboarding cannot find `lb_customers`, `lb_booking_services`, `lb_landing_pages`, or `lb_loyalty_cards`, or where an incomplete custom `lb_campaigns` schema lacks `type` and `settings`.

- [ ] **Step 2: Update production-shaped onboarding fixtures**

For `OnboardingUiContractTest.php` and `RootCauseInvestigationTest.php`:

- call `createFizaHubDefaultDataTables()` immediately after `bootProductionLikeSchema()`;
- call `dropFizaHubDefaultDataTables()` before dropping `lb_businesses`, teams, and users in `afterEach()`.

Do not change existing API assertions.

- [ ] **Step 3: Update the full UI fixture without losing its test-only fields**

In `createFullUiGrowthTables()`:

```php
createFizaHubDefaultDataTables();

Schema::table('lb_campaigns', function (Blueprint $table): void {
    $table->string('objective')->nullable();
});
```

Remove its local `Schema::create('lb_campaigns', ...)` block. Keep `lb_qr_scans`, submissions, bookings, feedback responses, and redemption tables. In `afterEach()`, drop those response/scan tables first, then call `dropFizaHubDefaultDataTables()`.

Extend the repeat count snapshot with:

```php
'default_campaigns' => QrCampaign::query()->whereIn('type', ['review', 'booking', 'coupon', 'feedback', 'lead'])->count(),
'landing_pages' => LandingPage::query()->count(),
```

Assert `default_campaigns = 5` and `landing_pages = 5` after replay.

- [ ] **Step 4: Update the cross-endpoint fixture**

In `createCrossEndpointTables()`, remove the local incomplete `lb_campaigns` block and call `createFizaHubDefaultDataTables()` after users, teams, and `lb_businesses` are created. Retain its response/scan tables. In `afterEach()`, drop response/scan tables first, then call `dropFizaHubDefaultDataTables()`.

- [ ] **Step 5: Run the affected suites and verify GREEN**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/OnboardingUiContractTest.php tests/Feature/APIPartnerFizaHUB/RootCauseInvestigationTest.php tests/Feature/APIPartnerFizaHUB/FullUiHappyPathTest.php tests/Feature/APIPartnerFizaHUB/PartnerCrossEndpointTest.php
```

Expected: all tests PASS with the original endpoint/status/support assertions intact and the full UI replay retaining exactly five defaults.

- [ ] **Step 6: Commit fixture alignment**

```powershell
git add tests/Feature/APIPartnerFizaHUB/OnboardingUiContractTest.php tests/Feature/APIPartnerFizaHUB/RootCauseInvestigationTest.php tests/Feature/APIPartnerFizaHUB/FullUiHappyPathTest.php tests/Feature/APIPartnerFizaHUB/PartnerCrossEndpointTest.php
git commit -m "test: align FizaHUB onboarding fixtures with default data"
```

---

### Task 4: Prove Admin status neutrality, deletion cleanup, and final regression safety

**Files:**
- Modify: `tests/Feature/APIPartnerFizaHUB/OnboardingAdminTransitionTest.php`
- Verify: `modules/APIPartnerFizaHUB/Services/OnboardingAdminService.php`
- Verify: `modules/AdminUser/Actions/DeleteUser.php`

**Interfaces:**
- Consumes: `FizaHubDefaultDataProvisioner::provision(...)` and existing Admin transition/deletion workflows.
- Produces: regression evidence that Admin statuses do not mutate defaults and user deletion removes them through existing ownership cascades.

- [ ] **Step 1: Replace the simplified customer fixture with the shared generated-data schema**

In `createAdminTransitionTables()`, remove its local `lb_customers` creation and call `createFizaHubDefaultDataTables()` after the business table exists. In `afterEach()`, call `dropFizaHubDefaultDataTables()` before dropping `lb_businesses`, teams, and users.

- [ ] **Step 2: Write a lifecycle regression test for status neutrality and deletion**

Import `FizaHubDefaultDataProvisioner`, `BookingService`, `Customer`, `LandingPage`, `LoyaltyCard`, and `QrCampaign`. Add this helper:

```php
function provisionAdminDefaultData(PartnerIntegration $integration): void
{
    $user = User::query()->findOrFail($integration->mlhub_user_id);
    $team = Team::query()->findOrFail($integration->mlhub_workspace_id);
    $business = LocalBusiness::query()->findOrFail($integration->mlhub_business_id);

    app(FizaHubDefaultDataProvisioner::class)->provision(
        $integration,
        $user,
        $team,
        $business
    );
}
```

Add this test:

```php
test('Admin status changes are data-neutral and existing user deletion removes every FizaHUB default', function (): void {
    $seed = seedAdminOnboarding();
    provisionAdminDefaultData($seed['integration']);

    $counts = [
        Customer::query()->count(),
        BookingService::query()->count(),
        LoyaltyCard::query()->count(),
        QrCampaign::query()->count(),
        LandingPage::query()->count(),
    ];

    $service = app(OnboardingAdminService::class);
    $service->transition($seed['onboarding'], OnboardingStatusMachine::CONSULTING, 'admin', 7);
    $service->transition($seed['onboarding']->fresh(), OnboardingStatusMachine::CONFIGURING, 'admin', 7);
    $service->transition($seed['onboarding']->fresh(), OnboardingStatusMachine::READY, 'admin', 7);

    expect([
        Customer::query()->count(),
        BookingService::query()->count(),
        LoyaltyCard::query()->count(),
        QrCampaign::query()->count(),
        LandingPage::query()->count(),
    ])->toBe($counts);

    $target = User::query()->findOrFail($seed['integration']->mlhub_user_id);
    $admin = User::query()->create([
        'name' => 'Admin',
        'username' => 'default_data_delete_admin',
        'email' => 'default-data-delete-admin@example.com',
        'password' => 'password-password-password-password-password-password-1234',
        'is_super_admin' => true,
    ]);
    app(DeleteUser::class)->execute($target, $admin->id);

    expect(Customer::query()->count())->toBe(0)
        ->and(BookingService::query()->count())->toBe(0)
        ->and(LoyaltyCard::query()->count())->toBe(0)
        ->and(QrCampaign::query()->count())->toBe(0)
        ->and(LandingPage::query()->count())->toBe(0)
        ->and(PartnerIntegration::query()->where('external_business_id', 'biz-admin')->exists())->toBeFalse();
});
```

- [ ] **Step 3: Run the lifecycle regression test**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB/OnboardingAdminTransitionTest.php --filter="Admin status changes are data-neutral"
```

Expected: PASS without production changes to `OnboardingAdminService` or `DeleteUser`. A failure means the existing ownership/deletion path is incompatible with the generated graph and must be reproduced in a narrower failing test before any production fix.

- [ ] **Step 4: Run the complete FizaHUB suite**

Run:

```powershell
php artisan test tests/Feature/APIPartnerFizaHUB
```

Expected: all FizaHUB feature tests PASS. If a failure exposes a real regression in existing status, support, package, webhook, identity, or deletion behavior, add a focused failing test before changing production code.

- [ ] **Step 5: Run formatter and complete project verification**

Run:

```powershell
vendor/bin/pint --test
php artisan test
```

Expected: Pint exits 0 and the complete application test suite passes with no errors or warnings attributable to this change.

- [ ] **Step 6: Commit final regression coverage**

```powershell
git add tests/Feature/APIPartnerFizaHUB/OnboardingAdminTransitionTest.php
git commit -m "test: cover FizaHUB default data lifecycle"
```

## Final Review Checklist

- [ ] Compare all eight approved default-data requirements with `campaignDefinition()`, customer, booking service, and loyalty attributes.
- [ ] Confirm the provisioner is called before onboarding success in both new and reuse paths.
- [ ] Confirm no Admin transition calls the provisioner.
- [ ] Confirm no queue job or `PlanLimitGuard` was introduced.
- [ ] Confirm integration metadata merges with existing tax, license, contact, and marketing-goal metadata.
- [ ] Confirm replay preserves edited campaign and landing-page content.
- [ ] Confirm deletion removes generated data and partner mappings.
- [ ] Confirm `git diff --check`, Pint, the FizaHUB suite, and the complete suite are clean.
