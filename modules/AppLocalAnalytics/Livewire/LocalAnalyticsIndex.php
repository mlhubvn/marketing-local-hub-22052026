<?php

namespace Modules\AppLocalAnalytics\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppQRCampaigns\Models\QrScan;
use Modules\AppReviewBooster\Models\ReviewFeedback;

#[Title('Reports')]
class LocalAnalyticsIndex extends Component
{
    use WithPagination;

    public string $tab = 'overview';
    public string $businessFilter = 'all';
    public string $dateRange = '30';
    public string $campaignType = 'all';
    public int $perPage = 10;

    public function mount(): void
    {
        $tab = (string) request()->query('tab', 'overview');

        if (in_array($tab, ['overview', 'leads', 'reviews'], true)) {
            $this->tab = $tab;
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['overview', 'leads', 'reviews'], true)) {
            $this->tab = $tab;
        }
    }

    public function updatedBusinessFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDateRange(): void
    {
        if (! in_array($this->dateRange, ['7', '30', '90', 'all'], true)) {
            $this->dateRange = '30';
        }

        $this->resetPage();
    }

    public function updatedCampaignType(): void
    {
        $this->resetPage();
    }

    public function exportCsv()
    {
        $userId = auth()->id();
        $businesses = LocalBusiness::query()->where('user_id', $userId)->get(['id', 'name']);
        $campaignQuery = QrCampaign::query()
            ->with('business')
            ->where('user_id', $userId)
            ->when($this->businessFilter !== 'all', fn ($query) => $query->where('business_id', (int) $this->businessFilter))
            ->when($this->campaignType !== 'all', fn ($query) => $query->where('type', $this->campaignType))
            ->when($this->dateRange !== 'all', fn ($query) => $query->where('created_at', '>=', now()->subDays((int) $this->dateRange)));

        $campaigns = $campaignQuery->withCount(['scans' => fn ($query) => $this->dateRange === 'all' ? $query : $query->where('created_at', '>=', now()->subDays((int) $this->dateRange))])->get();
        $campaignIds = $campaigns->pluck('id');
        $dateScope = fn ($query) => $this->dateRange === 'all' ? $query : $query->where('created_at', '>=', now()->subDays((int) $this->dateRange));

        $topCampaigns = $this->topCampaigns($campaigns, $campaignIds, $dateScope);
        $filename = 'mlhub-report-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($topCampaigns, $businesses): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Report', 'MKT AI']);
            fputcsv($out, ['Generated at', now()->toDateTimeString()]);
            fputcsv($out, ['Business filter', $this->businessFilter === 'all' ? 'All businesses' : (string) optional($businesses->firstWhere('id', (int) $this->businessFilter))->name]);
            fputcsv($out, ['Date range', $this->dateRange === 'all' ? 'All time' : 'Last '.$this->dateRange.' days']);
            fputcsv($out, ['Campaign type', $this->campaignType === 'all' ? 'All campaign types' : $this->campaignType]);
            fputcsv($out, []);
            fputcsv($out, ['Campaign', 'Business', 'Type', 'Visits', 'Leads', 'Bookings', 'Coupons', 'Review Clicks', 'Feedback', 'Conversion Rate']);

            foreach ($topCampaigns as $row) {
                fputcsv($out, [
                    $row['campaign']->name,
                    $row['campaign']->business?->name ?: '',
                    $row['campaign']->type,
                    $row['campaign']->scans_count,
                    $row['leads'],
                    $row['bookings'],
                    $row['coupons'],
                    $row['review_clicks'],
                    $row['feedback'],
                    $row['conversion_rate'].'%',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function render(): View
    {
        $userId = auth()->id();
        $businesses = LocalBusiness::query()->where('user_id', $userId)->orderBy('name')->get();

        if ($this->businessFilter !== 'all' && ! $businesses->contains('id', (int) $this->businessFilter)) {
            $this->businessFilter = 'all';
        }

        $campaignQuery = QrCampaign::query()
            ->where('user_id', $userId)
            ->when($this->businessFilter !== 'all', fn ($query) => $query->where('business_id', (int) $this->businessFilter))
            ->when($this->campaignType !== 'all', fn ($query) => $query->where('type', $this->campaignType))
            ->when($this->dateRange !== 'all', fn ($query) => $query->where('created_at', '>=', now()->subDays((int) $this->dateRange)));

        $campaignIds = (clone $campaignQuery)->pluck('id');
        $dateScope = fn ($query) => $this->dateRange === 'all' ? $query : $query->where('created_at', '>=', now()->subDays((int) $this->dateRange));
        $scans = $dateScope(QrScan::query()->where('user_id', $userId)->whereIn('campaign_id', $campaignIds))->count();
        $leads = $dateScope(LeadSubmission::query()->where('user_id', $userId)->whereIn('campaign_id', $campaignIds))->count();
        $bookings = $dateScope(Booking::query()->where('user_id', $userId)->whereIn('campaign_id', $campaignIds))->count();
        $coupons = $dateScope(CouponRedemption::query()->where('user_id', $userId)->whereIn('campaign_id', $campaignIds))->count();
        $feedbacks = $dateScope(ReviewFeedback::query()->where('user_id', $userId)->whereIn('campaign_id', $campaignIds))->count();
        $formFeedbacks = $dateScope(FeedbackResponse::query()->where('user_id', $userId)->whereIn('campaign_id', $campaignIds))->count();
        $reviewClicks = $dateScope(ReviewFeedback::query()->where('user_id', $userId)->whereIn('campaign_id', $campaignIds)->where('rating', '>=', 4))->count();
        $conversions = $leads + $bookings + $coupons + $feedbacks + $formFeedbacks + $reviewClicks;
        $reviewStats = $this->reviewStats($campaignIds, $dateScope);

        return view('applocalanalytics::index', [
            'businesses' => $businesses,
            'totals' => [
                'businesses' => $this->businessFilter === 'all' ? $businesses->count() : 1,
                'campaigns' => (clone $campaignQuery)->count(),
                'visits' => $scans,
                'scans' => $scans,
                'feedbacks' => $feedbacks,
                'form_feedbacks' => $formFeedbacks,
                'leads' => $leads,
                'bookings' => $bookings,
                'coupons' => $coupons,
                'review_clicks' => $reviewClicks,
                'conversion_rate' => $scans > 0 ? round(($conversions / $scans) * 100) : 0,
            ],
            'campaigns' => (clone $campaignQuery)
                ->with('business')
                ->withCount(['scans' => fn ($query) => $dateScope($query)])
                ->latest()
                ->paginate($this->perPage),
            'dailyScans' => QrScan::query()
                ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
                ->where('user_id', $userId)
                ->whereIn('campaign_id', $campaignIds)
                ->where('created_at', '>=', now()->subDays(14))
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('day')
                ->get(),
            'recentActivity' => $this->recentActivity($campaignIds, $dateScope),
            'topBusinesses' => $this->topBusinesses($businesses, $campaignIds, $dateScope),
            'topCampaigns' => $this->topCampaigns((clone $campaignQuery)->with('business')->withCount(['scans' => fn ($query) => $dateScope($query)])->get(), $campaignIds, $dateScope),
            'campaignTypePerformance' => $this->campaignTypePerformance((clone $campaignQuery)->get(), $campaignIds, $dateScope),
            'leadStats' => $this->leadStats($campaignIds, $dateScope, $scans),
            'leadSources' => $this->leadSources($campaignIds, $dateScope, $scans),
            'leadInbox' => $this->leadInbox($campaignIds, $dateScope),
            'bookingStats' => $this->bookingStats($campaignIds, $dateScope),
            'couponStats' => $this->couponStats($campaignIds, $dateScope),
            'reviewStats' => $reviewStats,
            'ratingDistribution' => $this->ratingDistribution($campaignIds, $dateScope),
            'reviewInbox' => $this->reviewInbox($campaignIds, $dateScope),
            'lowScoreFeedback' => $this->lowScoreFeedback($campaignIds, $dateScope),
            'topReviewBusinesses' => $this->topReviewBusinesses($businesses, $campaignIds, $dateScope),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Reports'),
        ]);
    }

    private function leadStats($campaignIds, callable $dateScope, int $visits): array
    {
        $leadForms = $dateScope(LeadSubmission::query()->whereIn('campaign_id', $campaignIds))->count();
        $bookings = $dateScope(Booking::query()->whereIn('campaign_id', $campaignIds))->count();
        $coupons = $dateScope(CouponRedemption::query()->whereIn('campaign_id', $campaignIds))->count();
        $feedback = $dateScope(FeedbackResponse::query()->whereIn('campaign_id', $campaignIds))->count();
        $total = $leadForms + $bookings + $coupons + $feedback;
        $converted = $dateScope(Booking::query()->whereIn('campaign_id', $campaignIds)->where('status', 'completed'))->count()
            + $dateScope(CouponRedemption::query()->whereIn('campaign_id', $campaignIds)->whereNotNull('used_at'))->count();

        return [
            'total' => $total,
            'new' => $total,
            'contacted' => 0,
            'converted' => $converted,
            'lost' => 0,
            'conversion_rate' => $visits > 0 ? round(($total / $visits) * 100) : 0,
        ];
    }

    private function leadSources($campaignIds, callable $dateScope, int $visits)
    {
        return collect([
            ['type' => __('Lead Form'), 'icon' => 'fa-light fa-user-plus', 'count' => $dateScope(LeadSubmission::query()->whereIn('campaign_id', $campaignIds))->count(), 'last' => $dateScope(LeadSubmission::query()->whereIn('campaign_id', $campaignIds))->latest()->value('created_at')],
            ['type' => __('Booking Page'), 'icon' => 'fa-light fa-calendar-check', 'count' => $dateScope(Booking::query()->whereIn('campaign_id', $campaignIds))->count(), 'last' => $dateScope(Booking::query()->whereIn('campaign_id', $campaignIds))->latest()->value('created_at')],
            ['type' => __('Coupon Claim'), 'icon' => 'fa-light fa-ticket', 'count' => $dateScope(CouponRedemption::query()->whereIn('campaign_id', $campaignIds))->count(), 'last' => $dateScope(CouponRedemption::query()->whereIn('campaign_id', $campaignIds))->latest()->value('created_at')],
            ['type' => __('Feedback Form'), 'icon' => 'fa-light fa-message-lines', 'count' => $dateScope(FeedbackResponse::query()->whereIn('campaign_id', $campaignIds))->count(), 'last' => $dateScope(FeedbackResponse::query()->whereIn('campaign_id', $campaignIds))->latest()->value('created_at')],
        ])->map(fn (array $source): array => [
            ...$source,
            'conversion_rate' => $visits > 0 ? round(($source['count'] / $visits) * 100) : 0,
        ]);
    }

    private function leadInbox($campaignIds, callable $dateScope)
    {
        $campaigns = QrCampaign::query()->with('business')->whereIn('id', $campaignIds)->get()->keyBy('id');

        return collect()
            ->merge($dateScope(LeadSubmission::query()->whereIn('campaign_id', $campaignIds))->latest()->limit(10)->get()->map(fn ($lead) => $this->leadRow($lead->name, $lead->email, $lead->phone, $lead->campaign_id, __('Lead Form'), __('New'), $lead->created_at, $campaigns)))
            ->merge($dateScope(Booking::query()->whereIn('campaign_id', $campaignIds))->latest()->limit(10)->get()->map(fn ($booking) => $this->leadRow($booking->customer_name, $booking->customer_email, $booking->customer_phone, $booking->campaign_id, __('Booking Page'), str($booking->status)->headline()->toString(), $booking->created_at, $campaigns)))
            ->merge($dateScope(CouponRedemption::query()->whereIn('campaign_id', $campaignIds))->latest()->limit(10)->get()->map(fn ($coupon) => $this->leadRow($coupon->customer_name, $coupon->customer_email, $coupon->customer_phone, $coupon->campaign_id, __('Coupon Claim'), str($coupon->status)->headline()->toString(), $coupon->created_at, $campaigns)))
            ->merge($dateScope(FeedbackResponse::query()->whereIn('campaign_id', $campaignIds))->latest()->limit(10)->get()->map(fn ($feedback) => $this->leadRow($feedback->customer_name ?: __('Guest'), $feedback->customer_email, $feedback->customer_phone, $feedback->campaign_id, __('Feedback Form'), __('New'), $feedback->created_at, $campaigns)))
            ->sortByDesc('created_at')
            ->take(10)
            ->values();
    }

    private function bookingStats($campaignIds, callable $dateScope): array
    {
        $query = $dateScope(Booking::query()->whereIn('campaign_id', $campaignIds));
        $total = (clone $query)->count();

        return [
            'total' => $total,
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'completed' => (clone $query)->where('status', 'completed')->count(),
            'cancelled' => (clone $query)->whereIn('status', ['cancelled', 'canceled'])->count(),
            'completion_rate' => $total > 0 ? round(((clone $query)->where('status', 'completed')->count() / $total) * 100) : 0,
        ];
    }

    private function couponStats($campaignIds, callable $dateScope): array
    {
        $query = $dateScope(CouponRedemption::query()->whereIn('campaign_id', $campaignIds));
        $claims = (clone $query)->count();
        $used = (clone $query)->where(function ($inner): void {
            $inner->where('status', 'used')->orWhereNotNull('used_at');
        })->count();

        $expiredCampaigns = QrCampaign::query()
            ->whereIn('id', $campaignIds)
            ->where('type', 'coupon')
            ->whereNotNull('settings->expiry_date')
            ->where('settings->expiry_date', '<', now()->toDateString())
            ->count();

        return [
            'claims' => $claims,
            'used' => $used,
            'unused' => max(0, $claims - $used),
            'expired_campaigns' => $expiredCampaigns,
            'redemption_rate' => $claims > 0 ? round(($used / $claims) * 100) : 0,
        ];
    }

    private function leadRow(?string $name, ?string $email, ?string $phone, ?int $campaignId, string $source, string $status, $createdAt, $campaigns): array
    {
        $campaign = $campaigns->get($campaignId);

        return [
            'name' => $name ?: __('Guest'),
            'email' => $email,
            'phone' => $phone,
            'campaign' => $campaign?->name ?: __('Campaign removed'),
            'business' => $campaign?->business?->name ?: __('No business'),
            'business_id' => $campaign?->business?->id,
            'source' => $source,
            'status' => $status,
            'created_at' => $createdAt,
        ];
    }

    private function reviewStats($campaignIds, callable $dateScope): array
    {
        $query = $dateScope(ReviewFeedback::query()->whereIn('campaign_id', $campaignIds));
        $totalRatings = (clone $query)->count();
        $lowScore = (clone $query)->where('rating', '<=', 3)->count();
        $positive = (clone $query)->where('rating', '>=', 4)->count();
        $avgRating = $totalRatings > 0 ? round((float) (clone $query)->avg('rating'), 1) : 0;

        return [
            'total_ratings' => $totalRatings,
            'avg_rating' => $avgRating,
            'google_review_clicks' => $positive,
            'low_score_feedback' => $lowScore,
            'resolved_feedback' => 0,
            'positive_ratings' => $positive,
            'negative_ratings' => $lowScore,
        ];
    }

    private function ratingDistribution($campaignIds, callable $dateScope)
    {
        $ratings = $dateScope(ReviewFeedback::query()->whereIn('campaign_id', $campaignIds))
            ->selectRaw('rating, COUNT(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating');

        $max = max(1, (int) $ratings->max());

        return collect([5, 4, 3, 2, 1])->map(fn (int $rating): array => [
            'rating' => $rating,
            'total' => (int) ($ratings[$rating] ?? 0),
            'percent' => round(((int) ($ratings[$rating] ?? 0) / $max) * 100),
        ]);
    }

    private function reviewInbox($campaignIds, callable $dateScope)
    {
        return $dateScope(ReviewFeedback::query()->with('campaign.business')->whereIn('campaign_id', $campaignIds))
            ->latest()
            ->limit(10)
            ->get();
    }

    private function lowScoreFeedback($campaignIds, callable $dateScope)
    {
        return $dateScope(ReviewFeedback::query()->with('campaign.business')->whereIn('campaign_id', $campaignIds)->where('rating', '<=', 3))
            ->latest()
            ->limit(5)
            ->get();
    }

    private function topReviewBusinesses($businesses, $campaignIds, callable $dateScope)
    {
        $campaigns = QrCampaign::query()->whereIn('id', $campaignIds)->get(['id', 'business_id']);

        return $businesses
            ->map(function (LocalBusiness $business) use ($campaigns, $dateScope): array {
                $ids = $campaigns->where('business_id', $business->id)->pluck('id');
                $query = $dateScope(ReviewFeedback::query()->whereIn('campaign_id', $ids));
                $ratings = (clone $query)->count();
                $lowScore = (clone $query)->where('rating', '<=', 3)->count();
                $googleClicks = (clone $query)->where('rating', '>=', 4)->count();

                return [
                    'business' => $business,
                    'ratings' => $ratings,
                    'avg_rating' => $ratings > 0 ? round((float) (clone $query)->avg('rating'), 1) : 0,
                    'google_clicks' => $googleClicks,
                    'low_score' => $lowScore,
                ];
            })
            ->filter(fn (array $row): bool => $row['ratings'] > 0)
            ->sortByDesc('ratings')
            ->take(6)
            ->values();
    }

    private function recentActivity($campaignIds, callable $dateScope)
    {
        $campaigns = QrCampaign::query()->with('business')->whereIn('id', $campaignIds)->get()->keyBy('id');

        return collect()
            ->merge($dateScope(LeadSubmission::query()->whereIn('campaign_id', $campaignIds))->latest()->limit(6)->get()->map(fn ($lead) => $this->activityRow($lead->name, __('submitted a lead form'), $lead->campaign_id, $campaigns, $lead->created_at, 'lead')))
            ->merge($dateScope(Booking::query()->whereIn('campaign_id', $campaignIds))->latest()->limit(6)->get()->map(fn ($booking) => $this->activityRow($booking->customer_name, __('booked an appointment'), $booking->campaign_id, $campaigns, $booking->created_at, $booking->status)))
            ->merge($dateScope(CouponRedemption::query()->whereIn('campaign_id', $campaignIds))->latest()->limit(6)->get()->map(fn ($coupon) => $this->activityRow($coupon->customer_name, __('claimed a coupon'), $coupon->campaign_id, $campaigns, $coupon->created_at, $coupon->status)))
            ->merge($dateScope(ReviewFeedback::query()->whereIn('campaign_id', $campaignIds))->latest()->limit(6)->get()->map(fn ($review) => $this->activityRow($review->customer_name ?: __('Guest'), $review->rating >= 4 ? __('clicked Google Review') : __('sent private feedback'), $review->campaign_id, $campaigns, $review->created_at, $review->rating >= 4 ? 'review' : 'feedback')))
            ->merge($dateScope(FeedbackResponse::query()->whereIn('campaign_id', $campaignIds))->latest()->limit(6)->get()->map(fn ($feedback) => $this->activityRow($feedback->customer_name ?: __('Guest'), __('sent feedback'), $feedback->campaign_id, $campaigns, $feedback->created_at, 'feedback')))
            ->sortByDesc('time')
            ->take(8)
            ->values();
    }

    private function activityRow(?string $customer, string $action, ?int $campaignId, $campaigns, $time, string $status): array
    {
        $campaign = $campaigns->get($campaignId);

        return [
            'customer' => $customer ?: __('Guest'),
            'action' => $action,
            'campaign' => $campaign?->name ?: __('Campaign removed'),
            'business' => $campaign?->business?->name ?: __('No business'),
            'time' => $time,
            'status' => $status,
        ];
    }

    private function topBusinesses($businesses, $campaignIds, callable $dateScope)
    {
        $campaigns = QrCampaign::query()->whereIn('id', $campaignIds)->get(['id', 'business_id']);

        return $businesses
            ->map(function (LocalBusiness $business) use ($campaigns, $dateScope): array {
                $ids = $campaigns->where('business_id', $business->id)->pluck('id');
                $visits = $dateScope(QrScan::query()->whereIn('campaign_id', $ids))->count();
                $leads = $dateScope(LeadSubmission::query()->whereIn('campaign_id', $ids))->count();
                $reviewClicks = $dateScope(ReviewFeedback::query()->whereIn('campaign_id', $ids)->where('rating', '>=', 4))->count();
                $bookings = $dateScope(Booking::query()->whereIn('campaign_id', $ids))->count();
                $coupons = $dateScope(CouponRedemption::query()->whereIn('campaign_id', $ids))->count();
                $conversions = $leads + $reviewClicks + $bookings + $coupons;

                return [
                    'business' => $business,
                    'campaigns' => $ids->count(),
                    'visits' => $visits,
                    'leads' => $leads,
                    'review_clicks' => $reviewClicks,
                    'bookings' => $bookings,
                    'coupons' => $coupons,
                    'conversion_rate' => $visits > 0 ? round(($conversions / $visits) * 100) : 0,
                ];
            })
            ->sortByDesc('visits')
            ->take(8)
            ->values();
    }

    private function topCampaigns($campaigns, $campaignIds, callable $dateScope)
    {
        $leads = $dateScope(LeadSubmission::query()->whereIn('campaign_id', $campaignIds))->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $bookings = $dateScope(Booking::query()->whereIn('campaign_id', $campaignIds))->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $coupons = $dateScope(CouponRedemption::query()->whereIn('campaign_id', $campaignIds))->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $reviews = $dateScope(ReviewFeedback::query()->whereIn('campaign_id', $campaignIds)->where('rating', '>=', 4))->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $feedback = $dateScope(FeedbackResponse::query()->whereIn('campaign_id', $campaignIds))->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');

        return $campaigns
            ->map(function (QrCampaign $campaign) use ($leads, $bookings, $coupons, $reviews, $feedback): array {
                $conversions = (int) ($leads[$campaign->id] ?? 0) + (int) ($bookings[$campaign->id] ?? 0) + (int) ($coupons[$campaign->id] ?? 0) + (int) ($reviews[$campaign->id] ?? 0) + (int) ($feedback[$campaign->id] ?? 0);

                return [
                    'campaign' => $campaign,
                    'leads' => (int) ($leads[$campaign->id] ?? 0),
                    'bookings' => (int) ($bookings[$campaign->id] ?? 0),
                    'coupons' => (int) ($coupons[$campaign->id] ?? 0),
                    'review_clicks' => (int) ($reviews[$campaign->id] ?? 0),
                    'feedback' => (int) ($feedback[$campaign->id] ?? 0),
                    'conversion_rate' => $campaign->scans_count > 0 ? round(($conversions / $campaign->scans_count) * 100) : 0,
                ];
            })
            ->sortByDesc(fn (array $row) => $row['campaign']->scans_count)
            ->take(8)
            ->values();
    }

    private function campaignTypePerformance($campaigns, $campaignIds, callable $dateScope)
    {
        return collect(['review', 'lead', 'booking', 'coupon', 'feedback'])
            ->map(function (string $type) use ($campaigns, $dateScope): array {
                $ids = $campaigns->where('type', $type)->pluck('id');

                return [
                    'type' => str($type)->headline()->toString(),
                    'visits' => $dateScope(QrScan::query()->whereIn('campaign_id', $ids))->count(),
                    'leads' => $dateScope(LeadSubmission::query()->whereIn('campaign_id', $ids))->count(),
                    'review_clicks' => $dateScope(ReviewFeedback::query()->whereIn('campaign_id', $ids)->where('rating', '>=', 4))->count(),
                    'bookings' => $dateScope(Booking::query()->whereIn('campaign_id', $ids))->count(),
                    'coupons' => $dateScope(CouponRedemption::query()->whereIn('campaign_id', $ids))->count(),
                    'feedback' => $dateScope(FeedbackResponse::query()->whereIn('campaign_id', $ids))->count(),
                ];
            });
    }
}
