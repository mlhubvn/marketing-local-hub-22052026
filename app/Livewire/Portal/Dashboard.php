<?php

namespace App\Livewire\Portal;

use App\Support\Plans\PlanLimitGuard;
use App\Support\Portal\PortalGrowthDashboardMetrics;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppQRCampaigns\Models\QrCampaign;

#[Title('User Dashboard')]
class Dashboard extends Component
{
    public array $recentActivity = [];
    public array $topCampaigns = [];

    public int $recentActivityLimit = 8;
    public bool $recentActivityHasMore = false;

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
        $user = auth()->user();
        $userId = $user?->id;

        $growthDashboard = $userId
            ? [
                'metrics' => PortalGrowthDashboardMetrics::rememberMetrics((int) $userId),
            ]
            : [
                'metrics' => [],
            ];
        $onboarding = $this->onboarding($userId, $growthDashboard);

        $planUsage = $userId
            ? Cache::remember(
                "portal.plan_usage.v1.{$userId}",
                now()->addMinutes(15),
                fn (): array => app(PlanLimitGuard::class)->usageSummary($user),
            )
            : [];

        return view(theme_view('livewire.portal.dashboard', 'app'), [
            'growthDashboard' => $growthDashboard,
            'onboarding' => $onboarding,
            'planUsage' => $planUsage,
            'welcomeItems' => user_dashboard_items($user, 'welcome'),
            'dashboardItems' => user_dashboard_items($user, 'main'),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('User Dashboard'),
        ]);
    }

    public function loadTopCampaigns(): void
    {
        $userId = auth()->id();

        if (! $userId) {
            return;
        }

        $this->topCampaigns = PortalGrowthDashboardMetrics::topCampaigns((int) $userId)->values()->all();
    }

    public function loadRecentActivity(): void
    {
        $userId = auth()->id();

        if (! $userId) {
            return;
        }

        $items = PortalGrowthDashboardMetrics::recentActivity((int) $userId, $this->recentActivityLimit);

        $this->recentActivity = $items->values()->all();
        $this->recentActivityHasMore = count($this->recentActivity) >= $this->recentActivityLimit;
    }

    public function loadMoreRecentActivity(): void
    {
        $this->recentActivityLimit = min(64, $this->recentActivityLimit + 8);

        $this->loadRecentActivity();
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
