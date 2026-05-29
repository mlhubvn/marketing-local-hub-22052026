<?php

namespace Modules\AppBusinessProfiles\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppCustomers\Models\Customer;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppQRCampaigns\Models\QrScan;
use Modules\AppReviewBooster\Models\ReviewFeedback;

#[Title('Business Reports')]
class BusinessReportsIndex extends Component
{
    public LocalBusiness $business;
    public string $dateRange = '30';
    public string $campaignType = 'all';
    public string $campaignFilter = 'all';
    public string $topSort = 'scans';

    public function mount(LocalBusiness $business): void
    {
        abort_unless((int) $business->user_id === (int) auth()->id(), 404);

        $this->business = $business;
    }

    public function updatedDateRange(): void
    {
        if (! in_array($this->dateRange, ['7', '30', '90', 'all'], true)) {
            $this->dateRange = '30';
        }
    }

    public function updatedCampaignType(): void
    {
        if (! in_array($this->campaignType, ['all', 'review', 'booking', 'coupon', 'feedback', 'lead'], true)) {
            $this->campaignType = 'all';
        }

        $this->campaignFilter = 'all';
    }

    public function updatedTopSort(): void
    {
        if (! in_array($this->topSort, ['scans', 'leads', 'bookings', 'coupons', 'reviews', 'conversion_rate'], true)) {
            $this->topSort = 'scans';
        }
    }

    public function render(): View
    {
        $campaigns = QrCampaign::query()
            ->where('user_id', auth()->id())
            ->where('business_id', $this->business->id)
            ->when($this->campaignType !== 'all', fn ($query) => $query->where('type', $this->campaignType))
            ->when($this->campaignFilter !== 'all', fn ($query) => $query->whereKey((int) $this->campaignFilter))
            ->withCount(['scans' => fn ($query) => $this->scopeDate($query)])
            ->get();

        if ($this->campaignFilter !== 'all' && ! $campaigns->contains('id', (int) $this->campaignFilter)) {
            $this->campaignFilter = 'all';
        }

        $campaignIds = $campaigns->pluck('id');
        $campaignNames = $campaigns->keyBy('id');
        $dateScope = fn ($query) => $this->dateRange === 'all' ? $query : $query->where('created_at', '>=', now()->subDays((int) $this->dateRange));

        $scans = $dateScope(QrScan::query()->whereIn('campaign_id', $campaignIds))->count();
        $leads = $dateScope(LeadSubmission::query()->whereIn('campaign_id', $campaignIds))->count();
        $bookings = $dateScope(Booking::query()->whereIn('campaign_id', $campaignIds))->count();
        $coupons = $dateScope(CouponRedemption::query()->whereIn('campaign_id', $campaignIds))->count();
        $feedbackForms = $dateScope(FeedbackResponse::query()->whereIn('campaign_id', $campaignIds))->count();
        $reviewFeedbacks = $dateScope(ReviewFeedback::query()->whereIn('campaign_id', $campaignIds))->count();
        $feedbacks = $feedbackForms + $reviewFeedbacks;
        $reviewClicks = $dateScope(ReviewFeedback::query()->whereIn('campaign_id', $campaignIds)->where('rating', '>=', 4))->count();
        $conversions = $leads + $bookings + $coupons + $feedbacks + $reviewClicks;

        $dailyActivity = $this->dailyActivity($campaignIds);
        $recentConversions = $this->recentConversions($campaignIds, $campaignNames);
        $reviewPerformance = $this->reviewPerformance($campaignIds, $reviewClicks);
        $leadSources = $this->leadSources($campaignIds, $campaignNames, $scans);
        $bookingPerformance = $this->bookingPerformance($campaignIds, $scans);
        $couponPerformance = $this->couponPerformance($campaignIds, $scans);
        $feedbackSummary = $this->feedbackSummary($campaignIds);
        $customerGrowth = $this->customerGrowth();

        return view('appbusinessprofiles::reports', [
            'totals' => [
                'campaigns' => $campaignIds->count(),
                'qr_scans' => $scans,
                'leads' => $leads,
                'bookings' => $bookings,
                'coupons' => $coupons,
                'feedback' => $feedbacks,
                'review_clicks' => $reviewClicks,
                'conversion_rate' => $scans > 0 ? round(($conversions / $scans) * 100) : 0,
                'conversions' => $conversions,
            ],
            'funnel' => [
                ['label' => __('Visits / QR Scans'), 'value' => $scans],
                ['label' => __('Review Clicks'), 'value' => $reviewClicks],
                ['label' => __('Leads'), 'value' => $leads],
                ['label' => __('Bookings'), 'value' => $bookings],
                ['label' => __('Coupon Claims'), 'value' => $coupons],
                ['label' => __('Feedback Submitted'), 'value' => $feedbacks],
            ],
            'campaignOptions' => QrCampaign::query()
                ->where('user_id', auth()->id())
                ->where('business_id', $this->business->id)
                ->when($this->campaignType !== 'all', fn ($query) => $query->where('type', $this->campaignType))
                ->orderBy('name')
                ->get(['id', 'name']),
            'dailyActivity' => $dailyActivity,
            'topCampaigns' => $this->topCampaigns($campaigns, $campaignIds),
            'recentConversions' => $recentConversions,
            'reviewPerformance' => $reviewPerformance,
            'leadSources' => $leadSources,
            'bookingPerformance' => $bookingPerformance,
            'couponPerformance' => $couponPerformance,
            'feedbackSummary' => $feedbackSummary,
            'customerGrowth' => $customerGrowth,
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('Reports').' - '.$this->business->name,
        ]);
    }

    private function topCampaigns(Collection $campaigns, Collection $campaignIds): Collection
    {
        $leads = $this->scopeDate(LeadSubmission::query()->whereIn('campaign_id', $campaignIds))->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $bookings = $this->scopeDate(Booking::query()->whereIn('campaign_id', $campaignIds))->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $coupons = $this->scopeDate(CouponRedemption::query()->whereIn('campaign_id', $campaignIds))->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $reviews = $this->scopeDate(ReviewFeedback::query()->whereIn('campaign_id', $campaignIds)->where('rating', '>=', 4))->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $feedback = $this->scopeDate(FeedbackResponse::query()->whereIn('campaign_id', $campaignIds))->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');

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
            ->sortByDesc(fn (array $row): int|float => match ($this->topSort) {
                'leads' => $row['leads'],
                'bookings' => $row['bookings'],
                'coupons' => $row['coupons'],
                'reviews' => $row['review_clicks'],
                'conversion_rate' => $row['conversion_rate'],
                default => $row['campaign']->scans_count,
            })
            ->take(8)
            ->values();
    }

    private function dailyActivity(Collection $campaignIds): Collection
    {
        $days = collect(range(13, 0))->mapWithKeys(fn (int $offset): array => [now()->subDays($offset)->toDateString() => [
            'day' => now()->subDays($offset)->toDateString(),
            'scans' => 0,
            'leads' => 0,
            'bookings' => 0,
            'coupons' => 0,
            'feedback' => 0,
            'review_clicks' => 0,
        ]]);

        $this->mergeDaily($days, QrScan::query()->whereIn('campaign_id', $campaignIds), 'scans');
        $this->mergeDaily($days, LeadSubmission::query()->whereIn('campaign_id', $campaignIds), 'leads');
        $this->mergeDaily($days, Booking::query()->whereIn('campaign_id', $campaignIds), 'bookings');
        $this->mergeDaily($days, CouponRedemption::query()->whereIn('campaign_id', $campaignIds), 'coupons');
        $this->mergeDaily($days, FeedbackResponse::query()->whereIn('campaign_id', $campaignIds), 'feedback');
        $this->mergeDaily($days, ReviewFeedback::query()->whereIn('campaign_id', $campaignIds)->where('rating', '>=', 4), 'review_clicks');

        return $days->values();
    }

    private function mergeDaily(Collection $days, mixed $query, string $key): void
    {
        $query
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->each(function ($row) use ($days, $key): void {
                if ($days->has($row->day)) {
                    $item = $days->get($row->day);
                    $item[$key] = (int) $row->total;
                    $days->put($row->day, $item);
                }
            });
    }

    private function recentConversions(Collection $campaignIds, Collection $campaignNames): Collection
    {
        return collect()
            ->merge($this->scopeDate(LeadSubmission::query()->whereIn('campaign_id', $campaignIds))->latest()->limit(8)->get()->map(fn ($lead): array => $this->conversionRow(__('submitted a lead form'), $lead->name, $lead->campaign_id, $campaignNames, $lead->created_at, 'lead')))
            ->merge($this->scopeDate(Booking::query()->whereIn('campaign_id', $campaignIds))->latest()->limit(8)->get()->map(fn ($booking): array => $this->conversionRow(__('booked an appointment'), $booking->customer_name, $booking->campaign_id, $campaignNames, $booking->created_at, $booking->status)))
            ->merge($this->scopeDate(CouponRedemption::query()->whereIn('campaign_id', $campaignIds))->latest()->limit(8)->get()->map(fn ($coupon): array => $this->conversionRow(__('claimed a coupon'), $coupon->customer_name, $coupon->campaign_id, $campaignNames, $coupon->created_at, $coupon->status)))
            ->merge($this->scopeDate(ReviewFeedback::query()->whereIn('campaign_id', $campaignIds))->latest()->limit(8)->get()->map(fn ($review): array => $this->conversionRow($review->rating >= 4 ? __('clicked Google Review') : __('sent private feedback'), $review->customer_name ?: __('Guest'), $review->campaign_id, $campaignNames, $review->created_at, $review->rating >= 4 ? 'positive' : 'needs reply')))
            ->merge($this->scopeDate(FeedbackResponse::query()->whereIn('campaign_id', $campaignIds))->latest()->limit(8)->get()->map(fn ($feedback): array => $this->conversionRow(__('sent feedback'), $feedback->customer_name ?: __('Guest'), $feedback->campaign_id, $campaignNames, $feedback->created_at, 'feedback')))
            ->sortByDesc('time')
            ->take(10)
            ->values();
    }

    private function conversionRow(string $action, ?string $customer, ?int $campaignId, Collection $campaignNames, mixed $time, string $status): array
    {
        return [
            'customer' => $customer ?: __('Guest'),
            'action' => $action,
            'campaign' => $campaignNames->get($campaignId)?->name ?: __('Campaign removed'),
            'time' => $time,
            'status' => $status,
        ];
    }

    private function reviewPerformance(Collection $campaignIds, int $reviewClicks): array
    {
        $ratings = $this->scopeDate(ReviewFeedback::query()->whereIn('campaign_id', $campaignIds));
        $total = (clone $ratings)->count();
        $positive = (clone $ratings)->where('rating', '>=', 4)->count();
        $negative = (clone $ratings)->where('rating', '<=', 3)->count();

        return [
            'visits' => $this->scopeDate(QrScan::query()->whereIn('campaign_id', QrCampaign::query()->whereIn('id', $campaignIds)->where('type', 'review')->pluck('id')))->count(),
            'positive' => $positive,
            'negative' => $negative,
            'review_clicks' => $reviewClicks,
            'average_rating' => round((float) (clone $ratings)->avg('rating'), 1),
            'conversion_rate' => $total > 0 ? round(($positive / $total) * 100) : 0,
        ];
    }

    private function leadSources(Collection $campaignIds, Collection $campaignNames, int $scans): Collection
    {
        return LeadSubmission::query()
            ->whereIn('campaign_id', $campaignIds)
            ->when($this->dateRange !== 'all', fn ($query) => $query->where('created_at', '>=', now()->subDays((int) $this->dateRange)))
            ->selectRaw('campaign_id, COUNT(*) as total, MAX(created_at) as last_lead')
            ->groupBy('campaign_id')
            ->get()
            ->map(fn ($row): array => [
                'source' => __('Lead Form'),
                'campaign' => $campaignNames->get($row->campaign_id)?->name ?: __('Campaign removed'),
                'leads' => (int) $row->total,
                'conversion_rate' => $scans > 0 ? round(((int) $row->total / $scans) * 100) : 0,
                'last_lead' => $row->last_lead,
            ]);
    }

    private function bookingPerformance(Collection $campaignIds, int $scans): array
    {
        $statuses = $this->scopeDate(Booking::query()->whereIn('campaign_id', $campaignIds))->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $total = (int) $statuses->sum();

        return [
            'total' => $total,
            'pending' => (int) ($statuses['pending'] ?? 0),
            'confirmed' => (int) ($statuses['confirmed'] ?? 0),
            'cancelled' => (int) ($statuses['cancelled'] ?? 0),
            'completed' => (int) ($statuses['completed'] ?? 0),
            'conversion_rate' => $scans > 0 ? round(($total / $scans) * 100) : 0,
        ];
    }

    private function couponPerformance(Collection $campaignIds, int $scans): array
    {
        $statuses = $this->scopeDate(CouponRedemption::query()->whereIn('campaign_id', $campaignIds))->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $total = (int) $statuses->sum();
        $used = (int) ($statuses['used'] ?? 0);

        return [
            'claims' => $total,
            'used' => $used,
            'expired' => (int) ($statuses['expired'] ?? 0),
            'redemption_rate' => $total > 0 ? round(($used / $total) * 100) : 0,
            'conversion_rate' => $scans > 0 ? round(($total / $scans) * 100) : 0,
        ];
    }

    private function feedbackSummary(Collection $campaignIds): array
    {
        $feedback = $this->scopeDate(FeedbackResponse::query()->whereIn('campaign_id', $campaignIds));
        $reviews = $this->scopeDate(ReviewFeedback::query()->whereIn('campaign_id', $campaignIds));
        $total = (clone $feedback)->count() + (clone $reviews)->count();
        $positive = (clone $feedback)->where('rating', '>=', 4)->count() + (clone $reviews)->where('rating', '>=', 4)->count();
        $negative = (clone $feedback)->where('rating', '<=', 3)->count() + (clone $reviews)->where('rating', '<=', 3)->count();
        $resolved = (clone $feedback)->where('status', 'resolved')->count() + (clone $reviews)->where('status', 'resolved')->count();

        return [
            'total' => $total,
            'positive' => $positive,
            'negative' => $negative,
            'resolved' => $resolved,
            'unresolved' => max($negative - $resolved, 0),
            'average_rating' => round((float) collect([(clone $feedback)->avg('rating'), (clone $reviews)->avg('rating')])->filter()->avg(), 1),
        ];
    }

    private function customerGrowth(): array
    {
        $customers = Customer::query()->where('user_id', auth()->id())->where('business_id', $this->business->id);
        $newCustomers = (clone $customers)->where('created_at', '>=', now()->subDays(30))->count();

        return [
            'total' => (clone $customers)->count(),
            'new' => $newCustomers,
            'returning' => max((clone $customers)->count() - $newCustomers, 0),
            'last_activity' => (clone $customers)->latest('updated_at')->value('updated_at'),
        ];
    }

    private function scopeDate(mixed $query): mixed
    {
        return $this->dateRange === 'all'
            ? $query
            : $query->where('created_at', '>=', now()->subDays((int) $this->dateRange));
    }
}
