<?php

namespace Modules\CustomMLHUB\Support\MLHUBAIAssistant;

use App\Support\Portal\PortalGrowthDashboardMetrics;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Schema;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppCustomers\Models\Customer;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppQRCampaigns\Models\QrScan;
use Modules\AppReviewBooster\Models\ReviewFeedback;

class MLHUBAIContextBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(int $userId): array
    {
        $now = CarbonImmutable::now();
        $weekStart = $now->startOfWeek();
        $lastWeekStart = $weekStart->subWeek();
        $lastWeekEnd = $weekStart->subSecond();

        $metrics = PortalGrowthDashboardMetrics::rememberMetrics($userId);
        $topCampaigns = PortalGrowthDashboardMetrics::topCampaigns($userId);
        $recentActivity = PortalGrowthDashboardMetrics::recentActivity($userId, 5);

        $campaignIds = QrCampaign::query()
            ->where('user_id', $userId)
            ->pluck('id');

        $newCustomersThisWeek = $this->countNewCustomers($userId, $weekStart, $now);
        $newCustomersLastWeek = $this->countNewCustomers($userId, $lastWeekStart, $lastWeekEnd);

        $weeklySignals = $this->weeklySignals($userId, $campaignIds, $weekStart, $now);
        $reviewStats = $this->reviewStats($userId, $campaignIds, $weekStart, $now);
        $activeCampaigns = $this->activeCampaignSummaries($userId, $campaignIds);

        return [
            'generated_at' => $now->toIso8601String(),
            'locale' => app()->getLocale(),
            'metrics' => $metrics,
            'top_campaigns' => array_slice($topCampaigns, 0, 5),
            'recent_activity' => $recentActivity,
            'customers' => [
                'new_this_week' => $newCustomersThisWeek,
                'new_last_week' => $newCustomersLastWeek,
                'delta' => $newCustomersThisWeek - $newCustomersLastWeek,
            ],
            'weekly_signals' => $weeklySignals,
            'reviews' => $reviewStats,
            'active_campaigns' => $activeCampaigns,
            'business_list' => $this->businessList($userId),
            'onboarding' => $this->onboardingHints($metrics),
        ];
    }

    /**
     * @return array{count: int, names: list<string>}
     */
    protected function businessList(int $userId): array
    {
        if (! Schema::hasTable('lb_businesses')) {
            return ['count' => 0, 'names' => []];
        }

        $names = LocalBusiness::query()
            ->where('user_id', $userId)
            ->orderBy('name')
            ->limit(12)
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        return [
            'count' => (int) LocalBusiness::query()->where('user_id', $userId)->count(),
            'names' => $names,
        ];
    }

    protected function countNewCustomers(int $userId, CarbonImmutable $from, CarbonImmutable $to): int
    {
        if (! Schema::hasTable('lb_customers')) {
            return 0;
        }

        return (int) Customer::query()
            ->where('user_id', $userId)
            ->whereBetween('created_at', [$from, $to])
            ->count();
    }

    /**
     * @return array<string, int>
     */
    protected function weeklySignals(int $userId, $campaignIds, CarbonImmutable $from, CarbonImmutable $to): array
    {
        if ($campaignIds->isEmpty()) {
            return [
                'leads' => 0,
                'bookings' => 0,
                'positive_reviews' => 0,
                'coupon_claims' => 0,
                'qr_scans' => 0,
            ];
        }

        return [
            'leads' => (int) LeadSubmission::query()
                ->where('user_id', $userId)
                ->whereIn('campaign_id', $campaignIds)
                ->whereBetween('created_at', [$from, $to])
                ->count(),
            'bookings' => (int) Booking::query()
                ->where('user_id', $userId)
                ->whereIn('campaign_id', $campaignIds)
                ->whereBetween('created_at', [$from, $to])
                ->count(),
            'positive_reviews' => (int) ReviewFeedback::query()
                ->where('user_id', $userId)
                ->whereIn('campaign_id', $campaignIds)
                ->where('rating', '>=', 4)
                ->whereBetween('created_at', [$from, $to])
                ->count(),
            'coupon_claims' => (int) CouponRedemption::query()
                ->where('user_id', $userId)
                ->whereIn('campaign_id', $campaignIds)
                ->whereBetween('created_at', [$from, $to])
                ->count(),
            'qr_scans' => (int) QrScan::query()
                ->where('user_id', $userId)
                ->whereIn('campaign_id', $campaignIds)
                ->whereBetween('created_at', [$from, $to])
                ->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function reviewStats(int $userId, $campaignIds, CarbonImmutable $from, CarbonImmutable $to): array
    {
        if ($campaignIds->isEmpty() || ! Schema::hasTable('lb_review_feedbacks')) {
            return [
                'count' => 0,
                'average_rating' => 0,
                'needs_reply' => 0,
                'positive_count' => 0,
            ];
        }

        $reviews = ReviewFeedback::query()
            ->where('user_id', $userId)
            ->whereIn('campaign_id', $campaignIds)
            ->whereBetween('created_at', [$from, $to])
            ->get(['rating', 'replied_at']);

        $count = $reviews->count();

        return [
            'count' => $count,
            'average_rating' => $count > 0 ? round($reviews->avg('rating'), 1) : 0,
            'needs_reply' => $reviews->filter(fn ($review): bool => $review->replied_at === null && (int) $review->rating >= 4)->count(),
            'positive_count' => $reviews->where('rating', '>=', 4)->count(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function activeCampaignSummaries(int $userId, $campaignIds): array
    {
        if ($campaignIds->isEmpty()) {
            return [];
        }

        return QrCampaign::query()
            ->where('user_id', $userId)
            ->whereNotNull('published_at')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get(['id', 'name', 'type', 'status'])
            ->map(function (QrCampaign $campaign): array {
                $visits = (int) QrScan::query()->where('campaign_id', $campaign->id)->count();
                $conversions = $this->campaignConversions($campaign->id);

                return [
                    'name' => $campaign->name,
                    'type' => (string) $campaign->type,
                    'visits' => $visits,
                    'conversions' => $conversions,
                ];
            })
            ->values()
            ->all();
    }

    protected function campaignConversions(int $campaignId): int
    {
        $reviewClicks = ReviewFeedback::query()->where('campaign_id', $campaignId)->where('rating', '>=', 4)->count();
        $leads = LeadSubmission::query()->where('campaign_id', $campaignId)->count();
        $bookings = Booking::query()->where('campaign_id', $campaignId)->count();
        $coupons = CouponRedemption::query()->where('campaign_id', $campaignId)->count();
        $feedback = FeedbackResponse::query()->where('campaign_id', $campaignId)->count()
            + ReviewFeedback::query()->where('campaign_id', $campaignId)->where('rating', '<=', 3)->count();

        return $reviewClicks + $leads + $bookings + $coupons + $feedback;
    }

    /**
     * @param  array<string, mixed>  $metrics
     * @return list<string>
     */
    protected function onboardingHints(array $metrics): array
    {
        $hints = [];

        if ((int) ($metrics['businesses'] ?? 0) === 0) {
            $hints[] = 'create_business';
        }

        if ((int) ($metrics['campaigns'] ?? 0) === 0) {
            $hints[] = 'create_campaign';
        }

        if ((int) ($metrics['active_campaigns'] ?? 0) === 0 && (int) ($metrics['campaigns'] ?? 0) > 0) {
            $hints[] = 'publish_campaign';
        }

        if ((int) ($metrics['visits'] ?? 0) === 0 && (int) ($metrics['active_campaigns'] ?? 0) > 0) {
            $hints[] = 'share_qr';
        }

        if ((int) ($metrics['review_clicks'] ?? 0) === 0) {
            $hints[] = 'boost_reviews';
        }

        return $hints;
    }
}
