<?php

use Illuminate\Support\Facades\Schema;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Services\FizaHubDefaultDataProvisioner;
use Modules\AppBookingPages\Models\BookingService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCustomers\Models\Customer;
use Modules\AppLandingPages\Models\LandingPage;
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
        ->and(data_get($integration->fresh()->metadata, 'tax_code'))->toBe('0400123456')
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
        ->not->toBe($coupon->id)
        ->and(data_get($seed['integration']->fresh()->metadata, 'tax_code'))->toBe('0400123456');
});
