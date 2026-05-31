<?php

namespace App\Livewire\Portal;

use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use App\Support\Plans\PlanLimitGuard;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppQRCampaigns\Models\QrScan;
use Modules\AppReviewBooster\Models\ReviewFeedback;

#[Title('User Dashboard')]
class Dashboard extends Component
{
    /**
     * @param  array<int, string>  $itemIds
     */
    public function saveLayout(array $itemIds): void
    {
        $payload = Validator::make([
            'item_ids' => $itemIds,
        ], [
            'item_ids' => ['required', 'array'],
            'item_ids.*' => ['string'],
        ])->validate();

        save_user_dashboard_layout(auth()->user(), $payload['item_ids']);
    }

    public function render(): View
    {
        $userId = auth()->id();
        $growthDashboard = $this->growthDashboard($userId);
        $onboarding = $this->onboarding($userId, $growthDashboard);

        return view(theme_view('livewire.portal.dashboard', 'app'), [
            'growthDashboard' => $growthDashboard,
            'onboarding' => $onboarding,
            'planUsage' => app(PlanLimitGuard::class)->usageSummary(auth()->user()),
            'welcomeItems' => user_dashboard_items(auth()->user(), 'welcome'),
            'dashboardItems' => user_dashboard_items(auth()->user(), 'main'),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('User Dashboard'),
        ]);
    }

    protected function growthDashboard(?int $userId): array
    {
        if (! $userId) {
            return [
                'metrics' => [],
                'recentActivity' => collect(),
                'topCampaigns' => collect(),
            ];
        }

        $campaignIds = QrCampaign::query()->where('user_id', $userId)->pluck('id');
        $visits = QrScan::query()->where('user_id', $userId)->whereIn('campaign_id', $campaignIds)->count();
        $reviewClicks = ReviewFeedback::query()->where('user_id', $userId)->whereIn('campaign_id', $campaignIds)->where('rating', '>=', 4)->count();
        $leads = LeadSubmission::query()->where('user_id', $userId)->whereIn('campaign_id', $campaignIds)->count();
        $bookings = Booking::query()->where('user_id', $userId)->whereIn('campaign_id', $campaignIds)->count();
        $couponClaims = CouponRedemption::query()->where('user_id', $userId)->whereIn('campaign_id', $campaignIds)->count();
        $feedback = FeedbackResponse::query()->where('user_id', $userId)->whereIn('campaign_id', $campaignIds)->count()
            + ReviewFeedback::query()->where('user_id', $userId)->whereIn('campaign_id', $campaignIds)->where('rating', '<=', 3)->count();
        $conversions = $reviewClicks + $leads + $bookings + $couponClaims + $feedback;

        return [
            'metrics' => [
                'businesses' => LocalBusiness::query()->where('user_id', $userId)->count(),
                'active_campaigns' => QrCampaign::query()->where('user_id', $userId)->whereNotNull('published_at')->count(),
                'visits' => $visits,
                'review_clicks' => $reviewClicks,
                'leads' => $leads,
                'bookings' => $bookings,
                'coupon_claims' => $couponClaims,
                'feedback' => $feedback,
                'conversion_rate' => $visits > 0 ? max(0, (int) round(($conversions / $visits) * 100)) : 0,
            ],
            'recentActivity' => $this->recentActivity($campaignIds),
            'topCampaigns' => $this->topCampaigns($userId, $campaignIds),
        ];
    }

    protected function recentActivity(Collection $campaignIds): Collection
    {
        $campaigns = QrCampaign::query()->with('business')->whereIn('id', $campaignIds)->get()->keyBy('id');

        return collect()
            ->merge(LeadSubmission::query()->whereIn('campaign_id', $campaignIds)->latest()->limit(8)->get()->map(fn ($lead): array => $this->activityRow($lead->name, __('submitted a lead'), $lead->campaign_id, $lead->created_at, $campaigns, 'fa-user-plus')))
            ->merge(Booking::query()->whereIn('campaign_id', $campaignIds)->latest()->limit(8)->get()->map(fn ($booking): array => $this->activityRow($booking->customer_name, __('booked an appointment'), $booking->campaign_id, $booking->created_at, $campaigns, 'fa-calendar-check')))
            ->merge(CouponRedemption::query()->whereIn('campaign_id', $campaignIds)->latest()->limit(8)->get()->map(fn ($coupon): array => $this->activityRow($coupon->customer_name, __('claimed a coupon'), $coupon->campaign_id, $coupon->created_at, $campaigns, 'fa-ticket')))
            ->merge(ReviewFeedback::query()->whereIn('campaign_id', $campaignIds)->latest()->limit(8)->get()->map(fn ($review): array => $this->activityRow($review->customer_name ?: __('Guest'), $review->rating >= 4 ? __('clicked review') : __('sent private feedback'), $review->campaign_id, $review->created_at, $campaigns, $review->rating >= 4 ? 'fa-star' : 'fa-message-lines')))
            ->merge(FeedbackResponse::query()->whereIn('campaign_id', $campaignIds)->latest()->limit(8)->get()->map(fn ($feedback): array => $this->activityRow($feedback->customer_name ?: __('Guest'), __('sent feedback'), $feedback->campaign_id, $feedback->created_at, $campaigns, 'fa-comments')))
            ->sortByDesc('time')
            ->take(8)
            ->values();
    }

    protected function activityRow(?string $customer, string $action, ?int $campaignId, mixed $time, Collection $campaigns, string $icon): array
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

    protected function topCampaigns(int $userId, Collection $campaignIds): Collection
    {
        $leads = LeadSubmission::query()->whereIn('campaign_id', $campaignIds)->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $bookings = Booking::query()->whereIn('campaign_id', $campaignIds)->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $coupons = CouponRedemption::query()->whereIn('campaign_id', $campaignIds)->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $reviews = ReviewFeedback::query()->whereIn('campaign_id', $campaignIds)->where('rating', '>=', 4)->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');
        $feedback = FeedbackResponse::query()->whereIn('campaign_id', $campaignIds)->selectRaw('campaign_id, COUNT(*) as total')->groupBy('campaign_id')->pluck('total', 'campaign_id');

        return QrCampaign::query()
            ->with('business')
            ->withCount('scans')
            ->where('user_id', $userId)
            ->get()
            ->map(function (QrCampaign $campaign) use ($leads, $bookings, $coupons, $reviews, $feedback): array {
                $conversions = (int) ($leads[$campaign->id] ?? 0)
                    + (int) ($bookings[$campaign->id] ?? 0)
                    + (int) ($coupons[$campaign->id] ?? 0)
                    + (int) ($reviews[$campaign->id] ?? 0)
                    + (int) ($feedback[$campaign->id] ?? 0);

                return [
                    'campaign' => $campaign,
                    'visits' => (int) $campaign->scans_count,
                    'conversions' => $conversions,
                    'conversion_rate' => $campaign->scans_count > 0 ? max(0, (int) round(($conversions / $campaign->scans_count) * 100)) : 0,
                ];
            })
            ->sortByDesc(fn (array $row): int|float => $row['conversions'] + $row['visits'])
            ->take(6)
            ->values();
    }

    protected function onboarding(?int $userId, array $growthDashboard): array
    {
        $metrics = $growthDashboard['metrics'] ?? [];
        $businesses = (int) ($metrics['businesses'] ?? 0);
        $activeCampaigns = (int) ($metrics['active_campaigns'] ?? 0);
        $visits = (int) ($metrics['visits'] ?? 0);
        $conversions = (int) ($metrics['review_clicks'] ?? 0)
            + (int) ($metrics['leads'] ?? 0)
            + (int) ($metrics['bookings'] ?? 0)
            + (int) ($metrics['coupon_claims'] ?? 0)
            + (int) ($metrics['feedback'] ?? 0);

        $campaigns = $userId ? QrCampaign::query()->where('user_id', $userId)->count() : 0;

        $steps = [
            [
                'key' => 'business',
                'title' => __('Create business'),
                'description' => __('Add the local profile that powers campaigns, QR codes, public pages, and reports.'),
                'icon' => 'fa-light fa-store',
                'complete' => $businesses > 0,
                'href' => $this->routeUrl('portal.businesses.create', 'portal.businesses'),
                'action' => $businesses > 0 ? __('Open businesses') : __('Create business'),
            ],
            [
                'key' => 'goal',
                'title' => __('Choose growth goal'),
                'description' => __('Pick review, booking, coupon, feedback, lead capture, landing page, or custom QR.'),
                'icon' => 'fa-light fa-bullseye-pointer',
                'complete' => $campaigns > 0,
                'href' => $this->routeUrl('portal.marketing-templates', 'portal.review-booster'),
                'action' => $campaigns > 0 ? __('Review goals') : __('Choose goal'),
            ],
            [
                'key' => 'launch',
                'title' => __('Launch campaign'),
                'description' => __('Publish a campaign page so customers can scan, open, claim, book, or submit.'),
                'icon' => 'fa-light fa-rocket-launch',
                'complete' => $activeCampaigns > 0,
                'href' => $this->routeUrl('portal.landing-pages', 'portal.review-booster'),
                'action' => $activeCampaigns > 0 ? __('View pages') : __('Launch campaign'),
            ],
            [
                'key' => 'share',
                'title' => __('Share QR / link'),
                'description' => __('Download the QR, copy the public link, and place it on posters, counters, invoices, or messages.'),
                'icon' => 'fa-light fa-qrcode',
                'complete' => $visits > 0,
                'href' => $this->routeUrl('portal.qr-campaigns', 'portal.landing-pages'),
                'action' => $visits > 0 ? __('Open QR library') : __('Share QR/link'),
            ],
            [
                'key' => 'track',
                'title' => __('Track results'),
                'description' => __('Watch scans, visits, leads, bookings, coupons, reviews, feedback, and conversion rate.'),
                'icon' => 'fa-light fa-chart-line',
                'complete' => $visits > 0 || $conversions > 0,
                'href' => $this->routeUrl('portal.reports', 'portal.dashboard'),
                'action' => __('Open reports'),
            ],
        ];

        $completed = collect($steps)->where('complete', true)->count();

        return [
            'steps' => $steps,
            'completed' => $completed,
            'total' => count($steps),
            'percent' => count($steps) > 0 ? (int) round(($completed / count($steps)) * 100) : 0,
            'is_complete' => $completed === count($steps),
        ];
    }

    protected function routeUrl(string $preferred, string $fallback): string
    {
        if (Route::has($preferred)) {
            return route($preferred);
        }

        if (Route::has($fallback)) {
            return route($fallback);
        }

        return url('/portal/dashboard');
    }
}
