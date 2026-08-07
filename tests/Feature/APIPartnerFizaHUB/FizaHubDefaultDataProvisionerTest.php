<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Services\FizaHubDefaultDataProvisioner;
use Modules\AppBookingPages\Models\BookingService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCustomers\Models\Customer;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppLandingPages\Support\LandingPageFactory;
use Modules\AppLandingPages\Support\PageTemplateCatalog;
use Modules\AppLoyaltyStampCards\Models\LoyaltyCard;
use Modules\AppQRCampaigns\Models\QrCampaign;

require_once __DIR__.'/FizaHubTestHelpers.php';

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
    $booking = QrCampaign::query()->where('type', 'booking')->firstOrFail();
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

    $pageContract = [
        'review' => [
            'campaign_id' => $review->id,
            'title' => 'Đánh giá trải nghiệm tại Cửa hàng Ánh Dương',
            'content' => [
                'headline' => 'Đánh giá trải nghiệm tại Cửa hàng Ánh Dương',
                'subheadline' => 'Hãy chọn mức đánh giá phù hợp với trải nghiệm của bạn.',
                'description' => 'Cảm ơn bạn đã tin tưởng và sử dụng sản phẩm, dịch vụ của chúng tôi. Đánh giá của bạn giúp chúng tôi phục vụ tốt hơn.',
                'cta' => 'Tiếp tục',
                'benefits' => ['Đánh giá nhanh chóng', 'Góp ý riêng tư khi trải nghiệm chưa tốt', 'Chia sẻ đánh giá công khai khi hài lòng'],
                'thank_you_message' => 'Cảm ơn bạn đã tin tưởng và sử dụng sản phẩm, dịch vụ của chúng tôi. Đánh giá của bạn giúp chúng tôi phục vụ tốt hơn.',
            ],
            'settings' => [
                'review_url' => 'https://www.google.com/maps/search/Cua+hang+Anh+Duong',
                'preferred_destination' => 'google',
                'positive_threshold' => 4,
                'negative_feedback_message' => 'Chúng tôi rất tiếc khi trải nghiệm của bạn chưa như mong đợi. Vui lòng chia sẻ thêm để chúng tôi có thể cải thiện.',
            ],
        ],
        'booking' => [
            'campaign_id' => $booking->id,
            'title' => 'Đặt lịch hẹn với Cửa hàng Ánh Dương',
            'content' => [
                'headline' => 'Đặt lịch tư vấn cùng Cửa hàng Ánh Dương',
                'subheadline' => 'Chọn dịch vụ, thời gian phù hợp và gửi yêu cầu đặt lịch.',
                'description' => 'Đặt lịch để được tư vấn về sản phẩm, dịch vụ và giải pháp phù hợp với nhu cầu của bạn.',
                'cta' => 'Đặt lịch ngay',
                'benefits' => ['Chọn dịch vụ tư vấn', 'Chọn khung giờ phù hợp', 'Nhận xác nhận từ cơ sở'],
                'thank_you_message' => 'Cảm ơn bạn. Chúng tôi đã nhận được yêu cầu đặt lịch.',
            ],
            'settings' => [
                'service' => 'Tư vấn sản phẩm và dịch vụ',
                'duration' => '60 phút',
                'price' => null,
                'available_days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'],
                'available_slots' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
                'use_business_hours' => true,
                'slot_interval' => 30,
                'buffer_before' => 0,
                'buffer_after' => 0,
            ],
        ],
        'coupon' => [
            'campaign_id' => $coupon->id,
            'title' => 'Ưu đãi chào mừng 10% tại Cửa hàng Ánh Dương',
            'content' => [
                'headline' => 'Ưu đãi chào mừng 10% tại Cửa hàng Ánh Dương',
                'subheadline' => 'Nhận ưu đãi chào mừng và xuất trình mã khi sử dụng dịch vụ.',
                'description' => 'Giảm 10% cho một lần sử dụng sản phẩm hoặc dịch vụ. Không áp dụng đồng thời với chương trình ưu đãi khác.',
                'cta' => 'Nhận ưu đãi',
                'benefits' => ['Ưu đãi 10%', 'Nhận mã ngay lập tức', 'Dễ dàng sử dụng tại cơ sở'],
                'thank_you_message' => 'Mã ưu đãi của bạn đã sẵn sàng.',
            ],
            'settings' => [
                'coupon_title' => 'CHAO10',
                'discount' => 'Giảm 10%',
                'expiry' => null,
                'terms' => 'Giảm 10% cho một lần sử dụng sản phẩm hoặc dịch vụ. Không áp dụng đồng thời với chương trình ưu đãi khác.',
            ],
        ],
        'feedback' => [
            'campaign_id' => $feedback->id,
            'title' => 'Đánh giá sản phẩm và dịch vụ tại Cửa hàng Ánh Dương',
            'content' => [
                'headline' => 'Bạn đánh giá thế nào về sản phẩm và dịch vụ của chúng tôi?',
                'subheadline' => 'Chia sẻ góp ý riêng để chúng tôi cải thiện trải nghiệm phục vụ.',
                'description' => '',
                'cta' => 'Gửi đánh giá',
                'benefits' => ['Góp ý riêng tư', 'Đánh giá trải nghiệm', 'Giúp chúng tôi phục vụ tốt hơn'],
                'thank_you_message' => 'Cảm ơn bạn đã dành thời gian đánh giá. Ý kiến của bạn giúp chúng tôi cải thiện chất lượng phục vụ.',
            ],
            'settings' => [
                'rating_required' => true,
                'contact_required' => false,
            ],
        ],
        'lead' => [
            'campaign_id' => $lead->id,
            'title' => 'Đăng ký nhận tư vấn từ Cửa hàng Ánh Dương',
            'content' => [
                'headline' => 'Để lại thông tin để được tư vấn sản phẩm và dịch vụ phù hợp',
                'subheadline' => 'Để lại thông tin để đội ngũ tư vấn liên hệ với bạn.',
                'description' => '',
                'cta' => 'Gửi yêu cầu tư vấn',
                'benefits' => ['Phản hồi nhanh chóng', 'Tư vấn phù hợp với nhu cầu', 'Quy trình đơn giản'],
                'thank_you_message' => 'Cảm ơn bạn. Chúng tôi đã nhận được yêu cầu tư vấn.',
            ],
            'settings' => [],
        ],
    ];

    foreach ($pageContract as $type => $expected) {
        $page = LandingPage::query()->where('type', $type)->sole();
        $template = PageTemplateCatalog::defaultForType($type);
        $design = PageTemplateCatalog::designFor($template);
        $design['template'] = $template;
        $expectedSettings = array_merge($expected['settings'], [
            'design' => $design,
            'blocks' => PageTemplateCatalog::blocksFor($type),
        ]);

        expect($page->campaign_id)->toBe($expected['campaign_id'])
            ->and($page->user_id)->toBe($user->id)
            ->and($page->business_id)->toBe($business->id)
            ->and($page->template)->toBe($template)
            ->and($page->title)->toBe($expected['title'])
            ->and($page->content)->toBe($expected['content'])
            ->and(array_keys($page->settings))->toEqualCanonicalizing(array_keys($expectedSettings));

        foreach ($expectedSettings as $key => $value) {
            expect(data_get($page->settings, $key))->toBe($value);
        }
    }

    $state = data_get($integration->fresh()->metadata, '_system.fizahub_default_data');
    expect($state['version'])->toBe('v1')
        ->and(data_get($integration->fresh()->metadata, 'tax_code'))->toBe('0400123456')
        ->and($state['customer_id'])->toBe($customer->id)
        ->and($state['booking_service_id'])->toBe($service->id)
        ->and($state['loyalty_card_id'])->toBe($card->id)
        ->and(array_keys($state['campaign_ids']))->toBe(['review', 'booking', 'coupon', 'feedback', 'lead'])
        ->and(array_keys((array) data_get($state, 'landing_page_ids')))->toBe(['review', 'booking', 'coupon', 'feedback', 'lead']);
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
    $couponPage = LandingPage::query()->where('campaign_id', $coupon->id)->firstOrFail();
    $couponPage->forceFill([
        'title' => 'Trang ưu đãi người dùng đã sửa',
        'content' => array_replace($couponPage->content, ['cta' => 'CTA người dùng đã sửa']),
    ])->save();
    $coupon->delete();

    $service->provision($seed['integration']->fresh(), $seed['user'], $seed['team'], $seed['business']);

    expect(QrCampaign::query()->count())->toBe(5)
        ->and(LandingPage::query()->count())->toBe(5)
        ->and(Customer::query()->count())->toBe(1)
        ->and(BookingService::query()->count())->toBe(1)
        ->and(LoyaltyCard::query()->count())->toBe(1)
        ->and($review->fresh()->name)->toBe('Tên người dùng đã sửa')
        ->and($reviewPage->fresh()->title)->toBe('Trang người dùng đã sửa')
        ->and($couponPage->fresh()->campaign_id)->not->toBeNull()
        ->and($couponPage->fresh()->title)->toBe('Trang ưu đãi người dùng đã sửa')
        ->and(data_get($couponPage->fresh()->content, 'cta'))->toBe('CTA người dùng đã sửa')
        ->and(data_get($seed['integration']->fresh()->metadata, '_system.fizahub_default_data.campaign_ids.coupon'))
        ->not->toBe($coupon->id)
        ->and(data_get($seed['integration']->fresh()->metadata, '_system.fizahub_default_data.landing_page_ids.coupon'))
        ->toBe($couponPage->id)
        ->and(data_get($seed['integration']->fresh()->metadata, 'tax_code'))->toBe('0400123456');
});

test('FizaHUB derived names preserve a valid 255 character business name and reserve bounded slug space', function (): void {
    $seed = seedDefaultDataOwner();
    $businessName = str_repeat('A', 255);
    $seed['business']->forceFill(['name' => $businessName])->save();
    $reviewName = 'Đánh giá trải nghiệm tại '.$businessName;
    $collidingSlug = Str::limit(Str::slug($reviewName), 255, '');
    $unrelatedCampaign = QrCampaign::query()->create([
        'user_id' => $seed['user']->id,
        'business_id' => $seed['business']->id,
        'slug' => $collidingSlug,
        'name' => 'Unrelated long-name collision',
        'type' => 'other',
        'status' => 'active',
        'settings' => [],
        'published_at' => now(),
    ]);
    $unrelatedPage = LandingPage::query()->create([
        'user_id' => $seed['user']->id,
        'business_id' => $seed['business']->id,
        'campaign_id' => null,
        'slug' => $collidingSlug,
        'title' => 'Unrelated orphan page',
        'type' => 'other',
        'template' => 'lead_capture',
        'status' => 'draft',
        'content' => ['cta' => 'Keep me'],
        'settings' => ['origin' => 'user'],
    ]);

    app(FizaHubDefaultDataProvisioner::class)->provision(
        $seed['integration'],
        $seed['user'],
        $seed['team'],
        $seed['business']->fresh()
    );

    $expectedNames = [
        'review' => 'Đánh giá trải nghiệm tại '.$businessName,
        'booking' => 'Đặt lịch hẹn với '.$businessName,
        'coupon' => 'Ưu đãi chào mừng 10% tại '.$businessName,
        'feedback' => 'Đánh giá sản phẩm và dịch vụ tại '.$businessName,
        'lead' => 'Đăng ký nhận tư vấn từ '.$businessName,
    ];

    foreach ($expectedNames as $type => $expectedName) {
        $campaign = QrCampaign::query()->where('type', $type)->sole();
        $page = LandingPage::query()->where('campaign_id', $campaign->id)->sole();

        expect($campaign->name)->toBe($expectedName)
            ->and($page->title)->toBe($expectedName)
            ->and(strlen($campaign->slug))->toBeLessThanOrEqual(255)
            ->and(strlen($page->slug))->toBeLessThanOrEqual(255);

        if ($type === 'review') {
            expect($campaign->slug)->toBe(Str::limit($collidingSlug, 253, '').'-2')
                ->and($page->slug)->toBe(Str::limit($collidingSlug, 253, '').'-2');
        }
    }

    expect($unrelatedCampaign->fresh()->name)->toBe('Unrelated long-name collision')
        ->and($unrelatedPage->fresh()->campaign_id)->toBeNull()
        ->and($unrelatedPage->fresh()->content)->toBe(['cta' => 'Keep me'])
        ->and($unrelatedPage->fresh()->settings)->toBe(['origin' => 'user']);
});

test('FizaHUB SQLite fixtures reach text storage through the production widening migration', function (): void {
    expect(Schema::getColumnType('lb_campaigns', 'name'))->toBe('text')
        ->and(Schema::getColumnType('lb_landing_pages', 'title'))->toBe('text');
});

test('forward migration widens the historical schema and persists full derived copy', function (): void {
    createFizaHubDefaultDataTables(false);

    expect(Schema::getColumnType('lb_campaigns', 'name'))->toBe('varchar')
        ->and(Schema::getColumnType('lb_landing_pages', 'title'))->toBe('varchar');

    $migration = require base_path('database/migrations/2026_08_08_000000_widen_fizahub_derived_copy_columns.php');
    $migration->up();

    expect(Schema::getColumnType('lb_campaigns', 'name'))->toBe('text')
        ->and(Schema::getColumnType('lb_landing_pages', 'title'))->toBe('text');

    $seed = seedDefaultDataOwner();
    $businessName = str_repeat('A', 255);
    $seed['business']->forceFill(['name' => $businessName])->save();

    app(FizaHubDefaultDataProvisioner::class)->provision(
        $seed['integration'],
        $seed['user'],
        $seed['team'],
        $seed['business']
    );

    $expectedCampaignName = 'Đánh giá trải nghiệm tại '.$businessName;
    $campaign = QrCampaign::query()->where('type', 'review')->sole();

    expect($campaign->name)->toBe($expectedCampaignName)
        ->and(LandingPage::query()->where('campaign_id', $campaign->id)->sole()->title)->toBe($expectedCampaignName);
});

test('landing page factory keeps generic defaults and applies explicit caller overrides', function (): void {
    $seed = seedDefaultDataOwner();
    $campaign = QrCampaign::query()->create([
        'user_id' => $seed['user']->id,
        'business_id' => $seed['business']->id,
        'slug' => 'generic-booking-page',
        'name' => 'Generic booking page',
        'type' => 'booking',
        'status' => 'active',
        'settings' => [],
        'published_at' => now(),
    ]);
    $factory = app(LandingPageFactory::class);

    $generic = $factory->syncFromCampaign($campaign);

    expect(data_get($generic->content, 'cta'))->toBe('Request booking')
        ->and(data_get($generic->settings, 'service'))->toBe('Appointment')
        ->and(data_get($generic->settings, 'available_slots'))->toBe(['09:00', '10:00', '14:00', '15:00']);

    $overridden = $factory->syncFromCampaign($campaign, [
        'content' => ['cta' => 'Đặt lịch ngay'],
        'settings' => [
            'service' => 'Tư vấn sản phẩm và dịch vụ',
            'duration' => '60 phút',
            'available_slots' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
        ],
    ]);

    expect(data_get($overridden->content, 'cta'))->toBe('Đặt lịch ngay')
        ->and(data_get($overridden->settings, 'service'))->toBe('Tư vấn sản phẩm và dịch vụ')
        ->and(data_get($overridden->settings, 'duration'))->toBe('60 phút')
        ->and(data_get($overridden->settings, 'available_slots'))->toBe(['09:00', '10:00', '11:00', '14:00', '15:00', '16:00']);
});

test('landing page factory keeps campaign identity when repairing a legacy generic page', function (): void {
    $seed = seedDefaultDataOwner();
    $campaign = QrCampaign::query()->create([
        'user_id' => $seed['user']->id,
        'business_id' => $seed['business']->id,
        'slug' => 'legacy-generic-booking-campaign',
        'name' => 'Legacy generic booking campaign',
        'type' => 'booking',
        'status' => 'active',
        'settings' => [],
        'published_at' => now(),
    ]);
    $legacy = LandingPage::query()->create([
        'user_id' => $seed['user']->id,
        'business_id' => null,
        'campaign_id' => $campaign->id,
        'slug' => 'legacy-generic-booking-page',
        'title' => 'Legacy page title',
        'type' => 'lead',
        'template' => 'lead_capture',
        'status' => 'draft',
        'content' => ['cta' => 'Legacy CTA'],
        'settings' => [],
    ]);

    $synced = app(LandingPageFactory::class)->syncFromCampaign($campaign);

    expect($synced->id)->toBe($legacy->id)
        ->and(LandingPage::query()->where('campaign_id', $campaign->id)->count())->toBe(1)
        ->and($synced->business_id)->toBe($seed['business']->id)
        ->and($synced->type)->toBe('booking')
        ->and(data_get($synced->content, 'cta'))->toBe('Request booking');
});
