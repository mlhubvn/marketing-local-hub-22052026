<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Support\PartnerApiException;
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

    private const SLUG_MAX_LENGTH = 255;

    public function __construct(private readonly LandingPageFactory $landingPages) {}

    public function provision(
        PartnerIntegration $integration,
        User $user,
        Team $team,
        LocalBusiness $business
    ): void {
        $state = $this->state($integration);
        $customer = $this->customer($state['customer_id'] ?? null, $user, $business);
        $bookingService = $this->bookingService($state['booking_service_id'] ?? null, $user, $business);
        $loyaltyCard = $this->loyaltyCard($state['loyalty_card_id'] ?? null, $user, $team, $business);

        $campaignIds = [];
        $landingPageIds = [];
        foreach (self::CAMPAIGN_TYPES as $type) {
            $trackedCampaignId = data_get($state, 'campaign_ids.'.$type);
            $campaign = $this->campaign(
                $type,
                $trackedCampaignId,
                $user,
                $business
            );
            $campaignIds[$type] = $campaign->id;
            $landingPageIds[$type] = $this->landingPage(
                $type,
                data_get($state, 'landing_page_ids.'.$type),
                $trackedCampaignId,
                $campaign,
                $user,
                $business,
                $bookingService
            )->id;
        }

        $nextState = array_replace($state, [
            'version' => self::VERSION,
            'customer_id' => $customer->id,
            'booking_service_id' => $bookingService->id,
            'loyalty_card_id' => $loyaltyCard->id,
        ]);
        $nextState['campaign_ids'] = array_replace(
            (array) data_get($state, 'campaign_ids', []),
            $campaignIds
        );
        $nextState['landing_page_ids'] = array_replace(
            (array) data_get($state, 'landing_page_ids', []),
            $landingPageIds
        );
        $metadata = (array) ($integration->metadata ?? []);
        data_set($metadata, self::STATE_PATH, $nextState);
        $integration->forceFill(['metadata' => $metadata])->save();
    }

    /** @return array<string, mixed> */
    private function state(PartnerIntegration $integration): array
    {
        $state = data_get($integration->metadata, self::STATE_PATH);

        if ($state === null) {
            return [];
        }

        if (! is_array($state)) {
            $this->integrationBroken();
        }

        $version = $state['version'] ?? null;

        if ($version !== null && $version !== self::VERSION) {
            $this->integrationBroken();
        }

        return $state;
    }

    private function customer(mixed $id, User $user, LocalBusiness $business): Customer
    {
        $trackedId = $this->trackedId($id);
        $customer = $trackedId === null ? null : Customer::query()->find($trackedId);

        if ($customer !== null) {
            if ((int) $customer->user_id !== (int) $user->id
                || (int) $customer->business_id !== (int) $business->id) {
                $this->integrationBroken();
            }

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
        $trackedId = $this->trackedId($id);
        $bookingService = $trackedId === null ? null : BookingService::query()->find($trackedId);

        if ($bookingService !== null) {
            if ((int) $bookingService->user_id !== (int) $user->id
                || (int) $bookingService->business_id !== (int) $business->id) {
                $this->integrationBroken();
            }

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
        $trackedId = $this->trackedId($id);
        $loyaltyCard = $trackedId === null ? null : LoyaltyCard::query()->find($trackedId);

        if ($loyaltyCard !== null) {
            if ((int) $loyaltyCard->user_id !== (int) $user->id
                || (int) $loyaltyCard->team_id !== (int) $team->id
                || (int) $loyaltyCard->business_id !== (int) $business->id) {
                $this->integrationBroken();
            }

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
        $trackedId = $this->trackedId($id);
        $campaign = $trackedId === null ? null : QrCampaign::query()->find($trackedId);

        if ($campaign !== null) {
            if ((int) $campaign->user_id !== (int) $user->id
                || (int) $campaign->business_id !== (int) $business->id
                || $campaign->type !== $type) {
                $this->integrationBroken();
            }

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

    private function landingPage(
        string $type,
        mixed $id,
        mixed $trackedCampaignId,
        QrCampaign $campaign,
        User $user,
        LocalBusiness $business,
        BookingService $bookingService
    ): LandingPage {
        $trackedPageId = $this->trackedId($id);
        $previousCampaignId = $this->trackedId($trackedCampaignId);
        $page = $trackedPageId === null ? null : LandingPage::query()->find($trackedPageId);

        if ($page !== null) {
            $this->assertPageOwnership($page, $type, $user, $business);

            if ((int) $page->campaign_id === (int) $campaign->id) {
                return $page;
            }

            $previousCampaignWasDeleted = $page->campaign_id === null
                && $previousCampaignId !== null
                && $previousCampaignId !== (int) $campaign->id
                && ! QrCampaign::query()->whereKey($previousCampaignId)->exists();

            if ($previousCampaignWasDeleted) {
                $page->forceFill(['campaign_id' => $campaign->id])->save();

                return $page;
            }

            $this->integrationBroken();
        }

        $attachedPages = LandingPage::query()
            ->where('campaign_id', $campaign->id)
            ->get();

        if ($attachedPages->isNotEmpty()) {
            if ($attachedPages->count() !== 1) {
                $this->integrationBroken();
            }

            $page = $attachedPages->firstOrFail();
            $this->assertPageOwnership($page, $type, $user, $business);

            return $page;
        }

        if ($trackedPageId === null
            && $previousCampaignId !== null
            && $previousCampaignId !== (int) $campaign->id) {
            $this->integrationBroken();
        }

        return $this->landingPages->syncFromCampaign(
            $campaign,
            $this->landingPageOverrides($type, $campaign, $bookingService)
        );
    }

    private function assertPageOwnership(
        LandingPage $page,
        string $type,
        User $user,
        LocalBusiness $business
    ): void {
        if ((int) $page->user_id !== (int) $user->id
            || (int) $page->business_id !== (int) $business->id
            || $page->type !== $type) {
            $this->integrationBroken();
        }
    }

    /** @return array{content: array<string, mixed>, settings: array<string, mixed>} */
    private function landingPageOverrides(
        string $type,
        QrCampaign $campaign,
        BookingService $bookingService
    ): array {
        $settings = (array) $campaign->settings;

        return match ($type) {
            'review' => [
                'content' => [
                    'headline' => $campaign->name,
                    'subheadline' => 'Hãy chọn mức đánh giá phù hợp với trải nghiệm của bạn.',
                    'description' => data_get($settings, 'thank_you_message', ''),
                    'cta' => 'Tiếp tục',
                    'benefits' => ['Đánh giá nhanh chóng', 'Góp ý riêng tư khi trải nghiệm chưa tốt', 'Chia sẻ đánh giá công khai khi hài lòng'],
                    'thank_you_message' => data_get($settings, 'thank_you_message', ''),
                ],
                'settings' => [
                    'preferred_destination' => data_get($settings, 'preferred_destination', 'google'),
                    'positive_threshold' => data_get($settings, 'positive_threshold', 4),
                    'negative_feedback_message' => data_get($settings, 'negative_feedback_message', ''),
                ],
            ],
            'booking' => [
                'content' => [
                    'headline' => data_get($settings, 'headline', $campaign->name),
                    'subheadline' => 'Chọn dịch vụ, thời gian phù hợp và gửi yêu cầu đặt lịch.',
                    'description' => (string) $bookingService->description,
                    'cta' => 'Đặt lịch ngay',
                    'benefits' => ['Chọn dịch vụ tư vấn', 'Chọn khung giờ phù hợp', 'Nhận xác nhận từ cơ sở'],
                    'thank_you_message' => 'Cảm ơn bạn. Chúng tôi đã nhận được yêu cầu đặt lịch.',
                ],
                'settings' => [
                    'service' => $bookingService->name,
                    'duration' => $bookingService->duration_minutes.' phút',
                    'price' => $bookingService->price,
                    'available_days' => $bookingService->available_days,
                    'available_slots' => $bookingService->time_slots,
                    'use_business_hours' => (bool) $bookingService->use_business_hours,
                    'slot_interval' => (int) $bookingService->slot_interval,
                    'buffer_before' => (int) $bookingService->buffer_before,
                    'buffer_after' => (int) $bookingService->buffer_after,
                ],
            ],
            'coupon' => [
                'content' => [
                    'headline' => $campaign->name,
                    'subheadline' => 'Nhận ưu đãi chào mừng và xuất trình mã khi sử dụng dịch vụ.',
                    'description' => data_get($settings, 'terms', ''),
                    'cta' => 'Nhận ưu đãi',
                    'benefits' => ['Ưu đãi 10%', 'Nhận mã ngay lập tức', 'Dễ dàng sử dụng tại cơ sở'],
                    'thank_you_message' => 'Mã ưu đãi của bạn đã sẵn sàng.',
                ],
                'settings' => [
                    'discount' => 'Giảm 10%',
                ],
            ],
            'feedback' => [
                'content' => [
                    'headline' => data_get($settings, 'headline', $campaign->name),
                    'subheadline' => 'Chia sẻ góp ý riêng để chúng tôi cải thiện trải nghiệm phục vụ.',
                    'description' => '',
                    'cta' => 'Gửi đánh giá',
                    'benefits' => ['Góp ý riêng tư', 'Đánh giá trải nghiệm', 'Giúp chúng tôi phục vụ tốt hơn'],
                    'thank_you_message' => data_get($settings, 'thank_you_message', ''),
                ],
                'settings' => [
                    'rating_required' => (bool) data_get($settings, 'rating_required', true),
                    'contact_required' => (bool) data_get($settings, 'contact_required', false),
                ],
            ],
            default => [
                'content' => [
                    'headline' => data_get($settings, 'headline', $campaign->name),
                    'subheadline' => 'Để lại thông tin để đội ngũ tư vấn liên hệ với bạn.',
                    'description' => '',
                    'cta' => 'Gửi yêu cầu tư vấn',
                    'benefits' => ['Phản hồi nhanh chóng', 'Tư vấn phù hợp với nhu cầu', 'Quy trình đơn giản'],
                    'thank_you_message' => 'Cảm ơn bạn. Chúng tôi đã nhận được yêu cầu tư vấn.',
                ],
                'settings' => [],
            ],
        };
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
        $counter = 1;

        do {
            $suffix = $counter === 1 ? '' : '-'.$counter;
            $candidate = Str::limit($base, self::SLUG_MAX_LENGTH - strlen($suffix), '').$suffix;
            $counter++;
        } while ($query->clone()->where('slug', $candidate)->exists());

        return $candidate;
    }

    private function trackedId(mixed $id): ?int
    {
        if ($id === null || $id === '') {
            return null;
        }

        if (! is_numeric($id) || (int) $id < 1) {
            $this->integrationBroken();
        }

        return (int) $id;
    }

    private function integrationBroken(): never
    {
        throw PartnerApiException::make(
            'integration_broken',
            __('Liên kết MKT không hợp lệ.'),
            409,
            ['next_action' => 'contact_support']
        );
    }
}
