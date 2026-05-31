<?php

namespace Modules\AdminFaker\Support;

use Database\Support\MlhubDemoVolume;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\AdminUser\Models\User;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTag;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBookingPages\Models\BookingService;
use Modules\AppBusinessLocations\Models\BusinessLocation;
use Modules\AppBusinessLocations\Support\LocationQrStyleCatalog;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppCustomers\Models\Customer;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppLandingPages\Support\LandingPageFactory;
use Modules\AppLandingPages\Support\PageTemplateCatalog;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppQRCampaigns\Models\QrScan;
use Modules\AppReviewBooster\Models\ReviewFeedback;

class MlhubLocalBoostDemoFaker
{
  public function seed(User $user, array &$counts): void
  {
    if (! class_exists(LocalBusiness::class) || ! class_exists(QrCampaign::class)) {
      return;
    }

    $this->clear($user, $counts);

    $config = MlhubAdminFakerConfig::load();
    $weeklyHours = $config['weekly_hours'];
    $messages = $config['messages'];
    $maxDaysAgo = (int) ($config['engagement_max_days_ago'] ?? 365);

    $businesses = collect($config['businesses'])->mapWithKeys(function (array $data, string $key) use ($user, $weeklyHours): array {
      $createdAt = now()->subDays(120 + (abs(crc32($key)) % 240));

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
            'zalo' => 'https://zalo.me/'.preg_replace('/\D+/', '', (string) ($data['zalo'] ?? $data['phone'])),
          ],
          'opening_hours' => $weeklyHours,
        ],
      );

      $business->forceFill([
        'created_at' => $createdAt,
        'updated_at' => now()->subDays(2),
      ])->save();

      return [$key => $business];
    });

    $counts['local_businesses'] = $businesses->count();

    if (class_exists(BusinessLocation::class)) {
      $locationCount = 0;

      foreach ($config['locations'] as $location) {
        $qrDesign = class_exists(LocationQrStyleCatalog::class)
          ? LocationQrStyleCatalog::designFor((string) $location['template'])
          : null;

        BusinessLocation::query()->updateOrCreate(
          [
            'user_id' => $user->id,
            'business_id' => $businesses[$location['business']]->id,
            'name' => $location['name'],
          ],
          [
            'phone' => $location['phone'],
            'email' => $location['email'],
            'address' => $location['address'],
            'google_maps_url' => $location['google_maps_url'],
            'opening_hours' => $weeklyHours,
            'qr_design' => $qrDesign,
            'is_active' => true,
          ],
        );

        $locationCount++;
      }

      $counts['local_locations'] = $locationCount;
    }

    $campaigns = collect($config['campaigns'])->mapWithKeys(function (array $data) use ($user, $businesses): array {
      $ageDays = (int) ($data['age_days'] ?? 30);
      $publishedAt = now()->subDays($ageDays);
      $key = (string) ($data['key'] ?? $data['slug']);

      $campaign = QrCampaign::query()->updateOrCreate(
        ['user_id' => $user->id, 'slug' => $data['slug']],
        [
          'business_id' => $businesses[$data['business']]->id,
          'name' => $data['name'],
          'type' => $data['type'],
          'settings' => array_merge($data['settings'], [
            'demo_marker' => DemoMarker::SOURCE,
            'demo_scope' => 'localboost-dn-soho',
          ]),
          'published_at' => $publishedAt,
          'created_at' => $publishedAt,
          'updated_at' => now()->subDays(min(14, $ageDays)),
        ],
      );

      app(LandingPageFactory::class)->syncFromCampaign($campaign);

      return [$key => $campaign];
    });

    $counts['local_campaigns'] = $campaigns->count();
    $counts['local_landing_pages'] += $campaigns->count();
    $counts['local_landing_pages'] += $this->createStandaloneLandingPages($user, $businesses, $config);

    $servicesByBusiness = collect($config['booking_services'])->map(function (array $service) use ($user, $businesses): BookingService {
      return BookingService::query()->updateOrCreate(
        [
          'user_id' => $user->id,
          'business_id' => $businesses[$service['business']]->id,
          'name' => $service['name'],
        ],
        [
          'duration_minutes' => $service['duration_minutes'],
          'price' => $service['price'],
          'description' => $service['description'],
          'available_days' => ['mon', 'tue', 'wed', 'thu', 'fri', 'sat'],
          'time_slots' => ['09:00', '10:00', '11:00', '14:00', '15:00', '16:00'],
          'use_business_hours' => true,
          'slot_interval' => 30,
          'buffer_before' => 0,
          'buffer_after' => 10,
          'service_hours' => null,
          'is_active' => true,
        ],
      );
    })->groupBy('business_id');

    $customers = collect($config['customers'])->map(function (array $row) use ($user, $businesses): Customer {
      $business = $businesses[$row['business']];
      $daysAgo = (int) ($row['created_days_ago'] ?? 30);

      return Customer::query()->updateOrCreate(
        [
          'user_id' => $user->id,
          'business_id' => $business->id,
          'phone' => $row['phone'],
        ],
        [
          'name' => $row['name'],
          'email' => $row['email'],
          'tags' => ['admin-faker', 'localboost', 'da-nang'],
          'metadata' => ['source' => DemoMarker::SOURCE, 'scope' => 'localboost-dn-soho'],
          'created_at' => now()->subDays($daysAgo),
          'updated_at' => now()->subDays(max(0, $daysAgo - 1)),
        ],
      );
    });

    $customerPool = $customers->map(fn (Customer $customer): object => (object) [
      'name' => $customer->name,
      'phone' => $customer->phone,
      'email' => $customer->email,
    ]);

    $totalVisits = 0;
    $totalLeads = 0;
    $totalBookings = 0;
    $totalCoupons = 0;
    $totalReviews = 0;
    $totalFeedback = 0;

    foreach ($campaigns->values() as $campaignIndex => $campaign) {
      $metrics = MlhubAdminFakerConfig::metricsForSlug($campaign->slug);
      $totalVisits += $metrics['visits'];

      MlhubDemoVolume::insertQrScans(
        $user->id,
        $campaign->id,
        $metrics['visits'],
        $config['scan_cities'],
        $config['scan_country'],
        $campaignIndex,
        $maxDaysAgo,
      );

      $couponCode = (string) data_get($campaign->settings, 'coupon_code', 'DNDEMO');

      $bookingServiceId = $servicesByBusiness->get($campaign->business_id)?->first()?->id
        ?? $servicesByBusiness->flatten()->first()?->id;

      match ($campaign->type) {
        'review' => (function () use ($user, $campaign, $metrics, $customerPool, $messages, $maxDaysAgo, &$totalReviews): void {
          $totalReviews += $metrics['conversions'];
          MlhubDemoVolume::insertReviewFeedback(
            $user->id,
            $campaign->id,
            $metrics['conversions'],
            $customerPool,
            $messages['review_positive'],
            $messages['review_negative'],
            $maxDaysAgo,
          );
        })(),
        'lead' => (function () use ($user, $campaign, $metrics, $customerPool, $messages, $maxDaysAgo, &$totalLeads): void {
          $totalLeads += $metrics['conversions'];
          MlhubDemoVolume::insertLeads(
            $user->id,
            $campaign->id,
            $metrics['conversions'],
            $customerPool,
            $messages['lead'],
            DemoMarker::SOURCE,
            $maxDaysAgo,
          );
        })(),
        'booking' => (function () use ($user, $campaign, $metrics, $customerPool, $messages, $bookingServiceId, $maxDaysAgo, &$totalBookings): void {
          if (! $bookingServiceId) {
            return;
          }
          $totalBookings += $metrics['conversions'];
          MlhubDemoVolume::insertBookings(
            $user->id,
            $campaign->id,
            $bookingServiceId,
            $metrics['conversions'],
            $customerPool,
            $messages['booking_note'],
            $maxDaysAgo,
          );
        })(),
        'coupon' => (function () use ($user, $campaign, $metrics, $customerPool, $couponCode, $maxDaysAgo, &$totalCoupons): void {
          $totalCoupons += $metrics['conversions'];
          MlhubDemoVolume::insertCouponRedemptions(
            $user->id,
            $campaign->id,
            $metrics['conversions'],
            $customerPool,
            $couponCode,
            $maxDaysAgo,
          );
        })(),
        'feedback' => (function () use ($user, $campaign, $metrics, $customerPool, $messages, $maxDaysAgo, &$totalFeedback): void {
          $totalFeedback += $metrics['conversions'];
          MlhubDemoVolume::insertFeedbackResponses(
            $user->id,
            $campaign->id,
            $metrics['conversions'],
            $customerPool,
            $messages['feedback_positive'],
            $messages['feedback_negative'],
            DemoMarker::SOURCE,
            $maxDaysAgo,
          );
        })(),
        default => null,
      };
    }

    $counts['local_qr_visits'] = $totalVisits;
    $counts['local_leads'] = $totalLeads;
    $counts['local_bookings'] = $totalBookings;
    $counts['local_coupon_claims'] = $totalCoupons;
    $counts['local_review_ratings'] = $totalReviews;
    $counts['local_low_score_feedback'] = (int) round($totalFeedback * 0.25);
    $counts['local_recent_activity'] = $totalLeads + $totalBookings + $totalCoupons + $totalReviews + $totalFeedback;
    $counts['local_top_campaigns'] = min(10, $campaigns->count());
    $counts['local_top_businesses'] = $businesses->count();

    $this->seedDemoCustomerTags($user, $customers);
  }

  protected function seedDemoCustomerTags(User $user, $customers): void
  {
    if (! class_exists(CustomerTag::class) || ! Schema::hasTable('lb_customer_tags')) {
      return;
    }

    $teamId = $user->ownedTeams()->value('id') ?? $user->id;

    $tags = collect([
      ['name' => 'VIP', 'slug' => 'vip', 'color' => '#f59e0b'],
      ['name' => 'Khách mới', 'slug' => 'new-customer', 'color' => '#2563eb'],
      ['name' => 'Khách quay lại', 'slug' => 'returning-customer', 'color' => '#0f766e'],
      ['name' => 'Cần follow-up', 'slug' => 'needs-follow-up', 'color' => '#dc2626'],
      ['name' => 'Đã nhận coupon', 'slug' => 'coupon-claimed', 'color' => '#7c3aed'],
      ['name' => 'Feedback điểm thấp', 'slug' => 'low-score-feedback', 'color' => '#ef4444'],
      ['name' => 'Khách thân thiết', 'slug' => 'loyal-customer', 'color' => '#16a34a'],
      ['name' => 'Giới thiệu', 'slug' => 'referral-customer', 'color' => '#0891b2'],
      ['name' => 'Khách Đà Nẵng', 'slug' => 'da-nang-local', 'color' => '#ff5f5f'],
    ])->map(fn (array $tag) => CustomerTag::query()->firstOrCreate(
      ['team_id' => $teamId, 'slug' => $tag['slug']],
      ['name' => $tag['name'], 'color' => $tag['color'], 'is_system' => true],
    ));

    foreach ($customers as $index => $customer) {
      $tag = $tags[$index % $tags->count()];

      $customer->crmTags()->syncWithoutDetaching([
        $tag->id => [
          'team_id' => $teamId,
          'created_by' => $user->id,
          'created_at' => now(),
        ],
      ]);
    }
  }

  public function clear(User $user, array &$deleted): void
  {
    if (! class_exists(QrCampaign::class)) {
      return;
    }

    $standaloneSlugs = MlhubAdminFakerConfig::standaloneLandingSlugs();

    if ($standaloneSlugs !== []) {
      $deleted['local_landing_pages'] += LandingPage::query()
        ->where('user_id', $user->id)
        ->whereIn('slug', $standaloneSlugs)
        ->delete();
    }

    $campaignIds = QrCampaign::query()
      ->where('user_id', $user->id)
      ->where(function ($query): void {
        $query->whereIn('slug', MlhubAdminFakerConfig::campaignSlugs())
          ->orWhere('slug', 'like', 'admin-faker-%');
      })
      ->pluck('id');

    if ($campaignIds->isNotEmpty()) {
      $deleted['local_leads'] += LeadSubmission::query()->whereIn('campaign_id', $campaignIds)->delete();
      $deleted['local_bookings'] += Booking::query()->whereIn('campaign_id', $campaignIds)->delete();
      $deleted['local_coupon_claims'] += CouponRedemption::query()->whereIn('campaign_id', $campaignIds)->delete();
      $deleted['local_review_ratings'] += ReviewFeedback::query()->whereIn('campaign_id', $campaignIds)->delete();
      $deleted['local_low_score_feedback'] += FeedbackResponse::query()->whereIn('campaign_id', $campaignIds)->where('rating', '<=', 3)->delete();
      FeedbackResponse::query()->whereIn('campaign_id', $campaignIds)->delete();
      $deleted['local_qr_visits'] += QrScan::query()->whereIn('campaign_id', $campaignIds)->delete();
      $deleted['local_landing_pages'] += LandingPage::query()->where('user_id', $user->id)->whereIn('campaign_id', $campaignIds)->delete();
      $deleted['local_campaigns'] += QrCampaign::query()->whereIn('id', $campaignIds)->delete();
    }

    $legacyNames = [
      'Sen Vàng Spa',
      'Cơm Nhà Bistro',
      'Nha Khoa An Nhiên',
    ];

    $businessIds = LocalBusiness::query()
      ->where('user_id', $user->id)
      ->where(function ($query) use ($legacyNames): void {
        $query->whereIn('name', MlhubAdminFakerConfig::businessNames())
          ->orWhereIn('name', $legacyNames);
      })
      ->pluck('id');

    if ($businessIds->isNotEmpty()) {
      if (class_exists(BusinessLocation::class)) {
        $deleted['local_locations'] += BusinessLocation::query()->where('user_id', $user->id)->whereIn('business_id', $businessIds)->delete();
      }
      BookingService::query()->where('user_id', $user->id)->whereIn('business_id', $businessIds)->delete();
      Customer::query()->where('user_id', $user->id)->whereIn('business_id', $businessIds)->delete();
      $deleted['local_businesses'] += LocalBusiness::query()->whereIn('id', $businessIds)->delete();
    }
  }

  protected function createStandaloneLandingPages(User $user, $businesses, array $config): int
  {
    if (! class_exists(LandingPage::class) || ! class_exists(PageTemplateCatalog::class)) {
      return 0;
    }

    return collect($config['standalone_landing_pages'] ?? [])
      ->map(function (array $data) use ($user, $businesses): LandingPage {
        $type = (string) $data['type'];
        $template = (string) $data['template'];
        $business = $businesses[$data['business']] ?? $businesses->first();
        $ageDays = (int) ($data['age_days'] ?? 7);
        $design = array_merge(
          PageTemplateCatalog::designFor($template),
          (array) ($data['design'] ?? [])
        );
        $content = [
          'headline' => $data['headline'],
          'subheadline' => $data['subheadline'],
          'description' => $data['description'] ?? '',
          'cta' => $data['cta'],
          'benefits' => $data['benefits'],
          'thank_you_message' => $data['thank_you_message'] ?? 'Cảm ơn bạn. Chúng tôi đã nhận yêu cầu.',
          'landing_page_blocks' => [],
        ];
        $settings = array_merge([
          'design' => $design,
          'blocks' => [],
          'demo_marker' => DemoMarker::SOURCE,
          'demo_scope' => 'localboost-dn-soho-landing',
        ], (array) ($data['settings'] ?? []));

        $scaledLandingMetrics = MlhubAdminFakerConfig::scaleMetrics(
          (int) ($data['visits'] ?? 0),
          (int) ($data['conversions'] ?? 0),
        );

        return LandingPage::query()->updateOrCreate(
          ['user_id' => $user->id, 'slug' => $data['slug']],
          [
            'business_id' => $business?->id,
            'campaign_id' => null,
            'title' => $data['title'],
            'type' => $type,
            'template' => $template,
            'status' => 'published',
            'content' => $content,
            'settings' => $settings,
            'visits_count' => $scaledLandingMetrics['visits'],
            'conversions_count' => $scaledLandingMetrics['conversions'],
            'published_at' => now()->subDays($ageDays),
            'created_at' => now()->subDays($ageDays),
            'updated_at' => now()->subDays(1),
          ],
        );
      })
      ->count();
  }
}
