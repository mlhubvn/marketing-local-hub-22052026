<?php

namespace Database\Seeders;

use Database\Support\IdSequence;
use Database\Support\MlhubDemoVolume;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\AdminPlans\Models\AdminPlan;
use Modules\AdminUser\Models\User;
use Modules\AdminUser\Support\PersonalTeamProvisioner;
use Modules\AppAffiliate\Support\AffiliateService;
use Modules\AppPayments\Support\UserPlanTransitionService;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBookingPages\Models\BookingService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppCustomers\Models\Customer;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLandingPages\Support\LandingPageFactory;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppQRCampaigns\Models\QrScan;
use Modules\AppReviewBooster\Models\ReviewFeedback;

class LocalBoostDemoSeeder extends Seeder
{
    protected array $demo;

    public function run(): void
    {
        $this->demo = MlhubDemoVolume::demoConfig();

        $user = $this->seedDemoUser();
        $businesses = $this->seedBusinesses($user);
        $campaigns = $this->seedCampaigns($user, $businesses);
        $service = $this->seedBookingService($user, $businesses);
        $customers = $this->seedCustomers($user, $businesses);

        $this->purgeEngagement($user, $campaigns->pluck('id'));
        $this->seedEngagementVolume($user, $campaigns, $service, $this->customerPool($customers));
    }

    protected function seedDemoUser(): User
    {
        $profile = $this->demo['user'];

        $user = User::query()->firstOrNew(['email' => $profile['email']]);

        if (! $user->exists) {
            $user->id = IdSequence::at(0);
        }

        $user->fill([
            'name' => $profile['name'],
            'username' => $profile['username'],
            'password' => Hash::make($profile['password']),
            'locale' => $profile['locale'],
            'timezone' => (string) config('mlhub.timezone', 'Asia/Ho_Chi_Minh'),
            'is_super_admin' => true,
            'email_verified_at' => now(),
        ])->save();

        if (class_exists(AffiliateService::class)) {
            $affiliate = app(AffiliateService::class);
            $affiliate->ensureReferralCode($user);
            $affiliate->ensureProfile($user);
        }

        if (class_exists(PersonalTeamProvisioner::class)) {
            app(PersonalTeamProvisioner::class)->ensureForUser($user);
        }

        $planSlug = trim((string) config('mlhub.admin_plan_slug', 'agency-lifetime'));
        $plan = $planSlug !== ''
            ? AdminPlan::query()->where('slug', $planSlug)->where('status', true)->first()
            : null;

        if ($plan instanceof AdminPlan && class_exists(UserPlanTransitionService::class)) {
            app(UserPlanTransitionService::class)->applyPurchasedPlan($user, $plan);
        }

        return $user;
    }

    protected function seedBusinesses(User $user)
    {
        $hours = $this->demo['weekly_hours'];
        $byName = [];

        return collect($this->demo['businesses'])->mapWithKeys(function (array $data, string $key) use ($user, $hours, &$byName) {
            if (isset($byName[$data['name']])) {
                return [$key => $byName[$data['name']]];
            }

            $business = LocalBusiness::query()->updateOrCreate(
                ['user_id' => $user->id, 'name' => $data['name']],
                [
                    'type' => $data['type'],
                    'phone' => $data['phone'],
                    'email' => $data['email'],
                    'website' => $data['website'],
                    'address' => $data['address'],
                    'google_maps_url' => $data['google_maps_url'],
                    'social_links' => [
                        'facebook' => 'https://facebook.com/'.Str::slug($data['name'], ''),
                        'zalo' => 'https://zalo.me/'.preg_replace('/\D+/', '', $data['phone']),
                    ],
                    'opening_hours' => $hours,
                ],
            );

            $byName[$data['name']] = $business;

            return [$key => $business];
        });
    }

    protected function seedCampaigns(User $user, $businesses)
    {
        return collect($this->demo['campaigns'])->mapWithKeys(function (array $data) use ($user, $businesses): array {
            $settings = $data['settings'];

            if ($data['type'] === 'coupon' && empty($settings['expiry_date'])) {
                $settings['expiry_date'] = now()->addDays(21)->toDateString();
            }

            $campaign = QrCampaign::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'user_id' => $user->id,
                    'business_id' => $businesses[$data['business']]->id,
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'settings' => $settings,
                    'published_at' => now()->subDays(14),
                ],
            );

            app(LandingPageFactory::class)->syncFromCampaign($campaign);

            return [$data['type'] => $campaign];
        });
    }

    protected function seedBookingService(User $user, $businesses): BookingService
    {
        $service = $this->demo['booking_service'];
        $business = $businesses[$service['business']];

        return BookingService::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'business_id' => $business->id,
                'name' => $service['name'],
            ],
            [
                'duration_minutes' => $service['duration_minutes'],
                'price' => $service['price'],
                'description' => $service['description'],
                'available_days' => $service['available_days'],
                'time_slots' => $service['time_slots'],
                'is_active' => true,
            ],
        );
    }

    protected function seedCustomers(User $user, $businesses)
    {
        return collect($this->demo['customers'])->map(fn (array $data) => Customer::query()->updateOrCreate(
            ['email' => $data['email']],
            [
                'user_id' => $user->id,
                'business_id' => $businesses[$data['business']]->id,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'tags' => ['demo', 'khach-hang-mau'],
                'metadata' => ['last_source' => 'mlhub_demo_vn'],
            ],
        ));
    }

    protected function customerPool(Collection $customers): Collection
    {
        $pool = $customers->map(fn (Customer $customer): object => (object) [
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email,
        ]);

        $extraNames = [
            'Võ Thanh Bình', 'Đặng Thu Hà', 'Bùi Quốc Huy', 'Ngô Kim Ngân', 'Trịnh Văn Long',
            'Phan Thị Yến', 'Đinh Hoàng Nam', 'Lý Minh Châu', 'Vũ Gia Hân', 'Cao Đức Anh',
        ];

        foreach ($extraNames as $index => $name) {
            $pool->push((object) [
                'name' => $name,
                'phone' => sprintf('09%02d %03d %03d', 20 + ($index % 70), 300 + $index, 400 + $index),
                'email' => 'khach.mau.'.($index + 1).'@demo.mlhub.vn',
            ]);
        }

        return $pool;
    }

    protected function purgeEngagement(User $user, Collection $campaignIds): void
    {
        if ($campaignIds->isEmpty()) {
            return;
        }

        QrScan::query()->where('user_id', $user->id)->whereIn('campaign_id', $campaignIds)->delete();
        LeadSubmission::query()->whereIn('campaign_id', $campaignIds)->delete();
        Booking::query()->whereIn('campaign_id', $campaignIds)->delete();
        CouponRedemption::query()->whereIn('campaign_id', $campaignIds)->delete();
        ReviewFeedback::query()->whereIn('campaign_id', $campaignIds)->delete();
        FeedbackResponse::query()->whereIn('campaign_id', $campaignIds)->delete();
    }

    protected function seedEngagementVolume(User $user, Collection $campaigns, BookingService $service, Collection $customerPool): void
    {
        $messages = $this->demo['messages'];
        $cities = $this->demo['scan_cities'];
        $country = $this->demo['scan_country'];
        $couponCode = collect($this->demo['campaigns'])->firstWhere('type', 'coupon')['settings']['coupon_code'] ?? 'CUOITUAN20';

        foreach ($this->demo['campaigns'] as $index => $definition) {
            $campaign = $campaigns[$definition['type']] ?? null;

            if (! $campaign instanceof QrCampaign) {
                continue;
            }

            $metrics = MlhubDemoVolume::metricsForSlug($definition['slug']);

            MlhubDemoVolume::insertQrScans(
                $user->id,
                $campaign->id,
                $metrics['visits'],
                $cities,
                $country,
                $index,
            );

            match ($definition['type']) {
                'review' => MlhubDemoVolume::insertReviewFeedback(
                    $user->id,
                    $campaign->id,
                    $metrics['conversions'],
                    $customerPool,
                    $messages['review_positive'],
                    $messages['review_negative'],
                ),
                'lead' => MlhubDemoVolume::insertLeads(
                    $user->id,
                    $campaign->id,
                    $metrics['conversions'],
                    $customerPool,
                    $messages['lead'],
                ),
                'booking' => MlhubDemoVolume::insertBookings(
                    $user->id,
                    $campaign->id,
                    $service->id,
                    $metrics['conversions'],
                    $customerPool,
                    $messages['booking_note'],
                ),
                'coupon' => MlhubDemoVolume::insertCouponRedemptions(
                    $user->id,
                    $campaign->id,
                    $metrics['conversions'],
                    $customerPool,
                    $couponCode,
                ),
                'feedback' => MlhubDemoVolume::insertFeedbackResponses(
                    $user->id,
                    $campaign->id,
                    $metrics['conversions'],
                    $customerPool,
                    $messages['feedback_positive'],
                    $messages['feedback_negative'],
                ),
                default => null,
            };
        }
    }
}
