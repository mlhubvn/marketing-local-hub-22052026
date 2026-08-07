<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\AppBookingPages\Models\BookingService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCustomers\Models\Customer;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppLandingPages\Support\LandingPageFactory;
use Modules\AppLandingPages\Support\PageTemplateCatalog;
use Modules\AppLoyaltyStampCards\Models\LoyaltyCard;
use Modules\AppQRCampaigns\Models\QrCampaign;

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

    private function customer(mixed $id, User $user, LocalBusiness $business): Customer
    {
        $customer = Customer::query()
            ->whereKey($id)
            ->where('user_id', $user->id)
            ->where('business_id', $business->id)
            ->first();

        if ($customer !== null) {
            return $customer;
        }

        return Customer::query()->create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'name' => $business->name,
            'phone' => $business->phone,
            'email' => $business->email ?: $user->email,
            'note' => 'Khách hàng đầu tiên được tạo tự động từ thông tin cơ sở kinh doanh.',
            'metadata' => ['source' => 'fizahub_onboarding'],
        ]);
    }

    private function bookingService(mixed $id, User $user, LocalBusiness $business): BookingService
    {
        $bookingService = BookingService::query()
            ->whereKey($id)
            ->where('user_id', $user->id)
            ->where('business_id', $business->id)
            ->first();

        if ($bookingService !== null) {
            return $bookingService;
        }

        return BookingService::query()->create([
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
    }

    private function loyaltyCard(mixed $id, User $user, Team $team, LocalBusiness $business): LoyaltyCard
    {
        $loyaltyCard = LoyaltyCard::query()
            ->whereKey($id)
            ->where('user_id', $user->id)
            ->where('business_id', $business->id)
            ->first();

        if ($loyaltyCard !== null) {
            return $loyaltyCard;
        }

        return LoyaltyCard::query()->create([
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
    }

    private function campaign(string $type, mixed $id, User $user, LocalBusiness $business): QrCampaign
    {
        $campaign = QrCampaign::query()
            ->whereKey($id)
            ->where('user_id', $user->id)
            ->where('business_id', $business->id)
            ->where('type', $type)
            ->first();

        if ($campaign !== null) {
            return $campaign;
        }

        $definition = $this->campaignDefinition($type, $business);

        return QrCampaign::query()->create([
            'user_id' => $user->id,
            'business_id' => $business->id,
            'slug' => $this->uniqueSlug(QrCampaign::query(), $definition['name'], 'fizahub-'.$type),
            'name' => $definition['name'],
            'type' => $type,
            'status' => 'active',
            'settings' => array_merge($this->defaultDesignSettingsForType($type), $definition['settings']),
            'published_at' => now(),
        ]);
    }

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

    private function defaultDesignSettingsForType(string $type): array
    {
        $landingTemplate = PageTemplateCatalog::defaultForType($type);

        return [
            'create_public_page' => true,
            'generate_qr_code' => true,
            'landing_template' => $landingTemplate,
            'design' => PageTemplateCatalog::designFor($landingTemplate),
        ];
    }

    private function googleName(string $name): string
    {
        return str_replace(' ', '+', Str::squish(Str::ascii($name)));
    }

    private function uniqueSlug(Builder $query, string $name, string $fallback): string
    {
        $base = Str::slug($name) ?: $fallback;
        $candidate = $base;
        $suffix = 2;

        while ($query->clone()->where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }
}
