<?php

namespace App\Support\Portal;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppQRCampaigns\Models\QrScan;
use Modules\AppReviewBooster\Models\ReviewFeedback;

class PortalGrowthDashboardMetrics
{
    public static function rememberMetrics(int $userId): array
    {
        return Cache::remember(
            "portal.growth_metrics.v1.{$userId}",
            now()->addMinutes(15),
            function () use ($userId): array {
                $campaignIds = QrCampaign::query()->where('user_id', $userId)->pluck('id');

                return self::metrics($userId, $campaignIds);
            },
        );
    }

    public static function forget(int $userId): void
    {
        Cache::forget("portal.growth_metrics.v1.{$userId}");
        Cache::forget("portal.top_campaigns.v1.{$userId}");
        Cache::forget("portal.recent_activity.v1.{$userId}");
        Cache::forget("portal.plan_usage.v1.{$userId}");
    }

    /**
     * @return array<string, int>
     */
    protected static function metrics(int $userId, Collection $campaignIds): array
    {
        if ($campaignIds->isEmpty()) {
            return [
                'businesses' => LocalBusiness::query()->where('user_id', $userId)->count(),
                'active_campaigns' => 0,
                'visits' => 0,
                'review_clicks' => 0,
                'leads' => 0,
                'bookings' => 0,
                'coupon_claims' => 0,
                'feedback' => 0,
                'conversion_rate' => 0,
            ];
        }

        $visits = (int) QrScan::query()->where('user_id', $userId)->count();
        $reviewClicks = ReviewFeedback::query()
            ->where('user_id', $userId)
            ->whereIn('campaign_id', $campaignIds)
            ->where('rating', '>=', 4)
            ->count();
        $leads = LeadSubmission::query()
            ->where('user_id', $userId)
            ->whereIn('campaign_id', $campaignIds)
            ->count();
        $bookings = Booking::query()
            ->where('user_id', $userId)
            ->whereIn('campaign_id', $campaignIds)
            ->count();
        $couponClaims = CouponRedemption::query()
            ->where('user_id', $userId)
            ->whereIn('campaign_id', $campaignIds)
            ->count();
        $feedback = FeedbackResponse::query()
            ->where('user_id', $userId)
            ->whereIn('campaign_id', $campaignIds)
            ->count()
            + ReviewFeedback::query()
                ->where('user_id', $userId)
                ->whereIn('campaign_id', $campaignIds)
                ->where('rating', '<=', 3)
                ->count();
        $conversions = $reviewClicks + $leads + $bookings + $couponClaims + $feedback;

        return [
            'businesses' => LocalBusiness::query()->where('user_id', $userId)->count(),
            'active_campaigns' => QrCampaign::query()->where('user_id', $userId)->whereNotNull('published_at')->count(),
            'visits' => $visits,
            'review_clicks' => $reviewClicks,
            'leads' => $leads,
            'bookings' => $bookings,
            'coupon_claims' => $couponClaims,
            'feedback' => $feedback,
            'conversion_rate' => $visits > 0 ? max(0, (int) round(($conversions / $visits) * 100)) : 0,
        ];
    }

    public static function recentActivity(int $userId, int $limit = 8): Collection
    {
        return Cache::remember(
            "portal.recent_activity.v1.{$userId}.{$limit}",
            now()->addMinutes(10),
            function () use ($userId, $limit): Collection {
                $campaignIds = QrCampaign::query()->where('user_id', $userId)->pluck('id');

                return self::recentActivityImpl($campaignIds, $limit);
            },
        );
    }

    public static function topCampaigns(int $userId): Collection
    {
        return Cache::remember(
            "portal.top_campaigns.v1.{$userId}",
            now()->addMinutes(15),
            function () use ($userId): Collection {
                $campaignIds = QrCampaign::query()->where('user_id', $userId)->pluck('id');

                return self::topCampaignsImpl($userId, $campaignIds);
            },
        );
    }

    protected static function recentActivityImpl(Collection $campaignIds, int $limit): Collection
    {
        if ($campaignIds->isEmpty()) {
            return collect();
        }

        $campaigns = QrCampaign::query()->with('business')->whereIn('id', $campaignIds)->get()->keyBy('id');

        return collect()
            ->merge(LeadSubmission::query()->whereIn('campaign_id', $campaignIds)->latest()->limit($limit)->get()->map(fn ($lead): array => self::activityRow($lead->name, __('submitted a lead'), $lead->campaign_id, $lead->created_at, $campaigns, 'fa-user-plus')))
            ->merge(Booking::query()->whereIn('campaign_id', $campaignIds)->latest()->limit($limit)->get()->map(fn ($booking): array => self::activityRow($booking->customer_name, __('booked an appointment'), $booking->campaign_id, $booking->created_at, $campaigns, 'fa-calendar-check')))
            ->merge(CouponRedemption::query()->whereIn('campaign_id', $campaignIds)->latest()->limit($limit)->get()->map(fn ($coupon): array => self::activityRow($coupon->customer_name, __('claimed a coupon'), $coupon->campaign_id, $coupon->created_at, $campaigns, 'fa-ticket')))
            ->merge(ReviewFeedback::query()->whereIn('campaign_id', $campaignIds)->latest()->limit($limit)->get()->map(fn ($review): array => self::activityRow($review->customer_name ?: __('Guest'), $review->rating >= 4 ? __('clicked review') : __('sent private feedback'), $review->campaign_id, $review->created_at, $campaigns, $review->rating >= 4 ? 'fa-star' : 'fa-message-lines')))
            ->merge(FeedbackResponse::query()->whereIn('campaign_id', $campaignIds)->latest()->limit($limit)->get()->map(fn ($feedback): array => self::activityRow($feedback->customer_name ?: __('Guest'), __('sent feedback'), $feedback->campaign_id, $feedback->created_at, $campaigns, 'fa-comments')))
            ->sortByDesc('time')
            ->take($limit)
            ->values();
    }

    /**
     * @return array{customer: string, action: string, campaign: string, business: string, time: mixed, icon: string}
     */
    protected static function activityRow(?string $customer, string $action, ?int $campaignId, mixed $time, Collection $campaigns, string $icon): array
    {
        $campaign = $campaigns->get($campaignId);

        return [
            'customer' => $customer ?: __('Guest'),
            'action' => $action,
            'campaign' => $campaign?->name ?: __('Campaign removed'),
            'business' => $campaign?->business?->name ?: __('No business'),
            'time' => $time,
            'icon' => $icon,
        ];
    }

    protected static function topCampaignsImpl(int $userId, Collection $campaignIds): Collection
    {
        if ($campaignIds->isEmpty()) {
            return collect();
        }

        $scanCounts = QrScan::query()
            ->where('user_id', $userId)
            ->whereIn('campaign_id', $campaignIds)
            ->selectRaw('campaign_id, COUNT(*) as total')
            ->groupBy('campaign_id')
            ->pluck('total', 'campaign_id');
        $leads = LeadSubmission::query()->whereIn('campaign_id', $campaignIds)->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $bookings = Booking::query()->whereIn('campaign_id', $campaignIds)->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $coupons = CouponRedemption::query()->whereIn('campaign_id', $campaignIds)->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $reviews = ReviewFeedback::query()->whereIn('campaign_id', $campaignIds)->where('rating', '>=', 4)->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $feedback = FeedbackResponse::query()->whereIn('campaign_id', $campaignIds)->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');

        return QrCampaign::query()
            ->with('business')
            ->where('user_id', $userId)
            ->get()
            ->map(function (QrCampaign $campaign) use ($leads, $bookings, $coupons, $reviews, $feedback, $scanCounts): array {
                $visits = (int) ($scanCounts[$campaign->id] ?? 0);
                $conversions = (int) ($leads[$campaign->id] ?? 0)
                    + (int) ($bookings[$campaign->id] ?? 0)
                    + (int) ($coupons[$campaign->id] ?? 0)
                    + (int) ($reviews[$campaign->id] ?? 0)
                    + (int) ($feedback[$campaign->id] ?? 0);

                return [
                    'campaign' => $campaign,
                    'visits' => $visits,
                    'conversions' => $conversions,
                    'conversion_rate' => $visits > 0 ? max(0, (int) round(($conversions / $visits) * 100)) : 0,
                ];
            })
            ->sortByDesc(fn (array $row): int|float => $row['conversions'] + $row['visits'])
            ->take(6)
            ->values();
    }
}
