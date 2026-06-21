<?php

namespace Modules\CustomMLHUB\Support\MLHUBAIAssistant;

use App\Support\Plans\PlanLimitGuard;
use App\Support\Portal\PortalGrowthDashboardMetrics;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Modules\AdminUser\Models\User;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppCustomers\Models\Customer;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppQRCampaigns\Models\QrScan;
use Modules\AppReviewBooster\Models\ReviewFeedback;
use Modules\AppTeams\Support\TeamWorkspaceAccess;

class MLHUBAIContextBuilder
{
    protected const CACHE_TTL_SECONDS = 60;

    /**
     * @return array<string, mixed>
     */
    public function build(int $userId): array
    {
        return Cache::remember(
            $this->contextCacheKey($userId),
            self::CACHE_TTL_SECONDS,
            fn (): array => $this->buildFresh($userId),
        );
    }

    protected function contextCacheKey(int $userId): string
    {
        return 'mlhub_ai_context:'.$userId.':team-'.(int) session('portal_team_id', 0).':'.app()->getLocale();
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildFresh(int $userId): array
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
        $workspaceOwner = $this->workspaceOwnerUser($userId);

        return [
            'generated_at' => $now->toIso8601String(),
            'locale' => app()->getLocale(),
            'user' => $this->userProfile($userId),
            'workspace' => $this->workspaceSnapshot($userId, $workspaceOwner),
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
            'plan' => $this->planSnapshot($workspaceOwner),
            'credits' => $this->creditSnapshot($workspaceOwner),
        ];
    }

    protected function workspaceOwnerUser(int $userId): ?User
    {
        try {
            $user = User::query()
                ->with(['plan', 'nextPlan'])
                ->find($userId);

            if (! $user) {
                return null;
            }

            $ownerId = $userId;

            if (class_exists(TeamWorkspaceAccess::class)) {
                $ownerId = TeamWorkspaceAccess::workspaceOwnerUserId($user);
            }

            if ($ownerId > 0 && $ownerId !== $userId) {
                return User::query()
                    ->with(['plan', 'nextPlan'])
                    ->find($ownerId) ?: $user;
            }

            return $user;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function workspaceSnapshot(int $userId, ?User $workspaceOwner): array
    {
        if (! $workspaceOwner) {
            return [
                'available' => false,
                'reason' => 'user_not_found',
                'user_id' => $userId,
                'owner_user_id' => $userId,
                'owner_is_current_user' => true,
            ];
        }

        return [
            'available' => true,
            'user_id' => $userId,
            'owner_user_id' => (int) $workspaceOwner->id,
            'owner_is_current_user' => (int) $workspaceOwner->id === $userId,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function planSnapshot(?User $workspaceOwner): array
    {
        if (! $workspaceOwner) {
            return ['available' => false, 'reason' => 'user_not_found'];
        }

        try {
            $usage = [];

            if (class_exists(PlanLimitGuard::class)) {
                try {
                    $usage = $this->normalizePlanUsage(app(PlanLimitGuard::class)->usageSummary($workspaceOwner));
                } catch (\Throwable) {
                    return [
                        'available' => false,
                        'reason' => 'usage_query_failed',
                        'name' => (string) $workspaceOwner->portalPlanLabel(),
                        'status' => (string) $workspaceOwner->portalPlanStatusLabel(),
                    ];
                }
            } else {
                return [
                    'available' => false,
                    'reason' => 'missing_plan_guard',
                    'name' => (string) $workspaceOwner->portalPlanLabel(),
                    'status' => (string) $workspaceOwner->portalPlanStatusLabel(),
                ];
            }

            return [
                'available' => true,
                'name' => (string) $workspaceOwner->portalPlanLabel(),
                'status' => (string) $workspaceOwner->portalPlanStatusLabel(),
                'is_trial' => (bool) $workspaceOwner->isInPlanTrial(),
                'starts_at' => $workspaceOwner->plan_started_at?->toIso8601String(),
                'expires_at' => $workspaceOwner->plan_expires_at?->toIso8601String(),
                'next_plan_name' => (string) ($workspaceOwner->nextPlan?->name ?? ''),
                'limits' => $this->planLimitsFromUsage($usage),
                'usage' => $usage,
                'usage_percent' => $this->highestUsagePercent($usage),
                'near_limit' => $this->nearLimitRows($usage),
            ];
        } catch (\Throwable) {
            return ['available' => false, 'reason' => 'query_failed'];
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function creditSnapshot(?User $workspaceOwner): array
    {
        if (! $workspaceOwner) {
            return ['available' => false, 'reason' => 'user_not_found'];
        }

        if (! function_exists('credit_summary') && ! method_exists($workspaceOwner, 'creditSummary')) {
            return ['available' => false, 'reason' => 'missing_helper'];
        }

        try {
            $summary = function_exists('credit_summary')
                ? credit_summary($workspaceOwner)
                : $workspaceOwner->creditSummary();
            $cost = null;

            try {
                if (function_exists('credit_service')) {
                    $cost = credit_service()->costFor($workspaceOwner, 'mlhub_ai_chat', 1);
                }
            } catch (\Throwable) {
                $cost = null;
            }

            return [
                'available' => true,
                'limit' => $this->nullableInt($summary['limit'] ?? null),
                'used' => (int) ($summary['used'] ?? 0),
                'remaining' => $this->nullableInt($summary['remaining'] ?? null),
                'plan_remaining' => $this->nullableInt($summary['plan_remaining'] ?? null),
                'topup_remaining' => (int) ($summary['topup_remaining'] ?? 0),
                'total_remaining' => $this->nullableInt($summary['total_remaining'] ?? ($summary['remaining'] ?? null)),
                'unlimited' => (bool) ($summary['unlimited'] ?? false),
                'low_balance' => (bool) ($summary['low_balance'] ?? false),
                'started_at' => $this->dateToIsoString($summary['started_at'] ?? null),
                'expires_at' => $this->dateToIsoString($summary['expires_at'] ?? null),
                'costs' => [
                    'mlhub_ai_chat' => $cost,
                ],
            ];
        } catch (\Throwable) {
            return ['available' => false, 'reason' => 'query_failed'];
        }
    }

    /**
     * @param  array<string, array<string, mixed>>  $usage
     * @return array<string, array<string, mixed>>
     */
    protected function normalizePlanUsage(array $usage): array
    {
        return collect($usage)
            ->map(function (array $row): array {
                return [
                    'label' => (string) ($row['label'] ?? ''),
                    'key' => (string) ($row['key'] ?? ''),
                    'used' => (int) ($row['used'] ?? 0),
                    'limit' => $this->nullableInt($row['limit'] ?? null),
                    'remaining' => $this->nullableInt($row['remaining'] ?? null),
                    'unlimited' => (bool) ($row['unlimited'] ?? false),
                    'percent' => (int) ($row['percent'] ?? 0),
                    'is_full' => (bool) ($row['is_full'] ?? false),
                ];
            })
            ->all();
    }

    /**
     * @param  array<string, array<string, mixed>>  $usage
     * @return array<string, int|null>
     */
    protected function planLimitsFromUsage(array $usage): array
    {
        return collect($usage)
            ->mapWithKeys(fn (array $row, $key): array => [(string) $key => $this->nullableInt($row['limit'] ?? null)])
            ->all();
    }

    /**
     * @param  array<string, array<string, mixed>>  $usage
     */
    protected function highestUsagePercent(array $usage): int
    {
        return (int) collect($usage)
            ->pluck('percent')
            ->filter(fn ($percent): bool => is_numeric($percent))
            ->max() ?: 0;
    }

    /**
     * @param  array<string, array<string, mixed>>  $usage
     * @return list<string>
     */
    protected function nearLimitRows(array $usage): array
    {
        return collect($usage)
            ->filter(fn (array $row): bool => (bool) ($row['is_full'] ?? false) || (int) ($row['percent'] ?? 0) >= 80)
            ->map(fn (array $row): string => (string) ($row['label'] ?? ''))
            ->filter()
            ->values()
            ->all();
    }

    protected function nullableInt(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }

    protected function dateToIsoString(mixed $value): ?string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        return null;
    }

    /**
     * @return array{name: string, short_name: string}
     */
    protected function userProfile(int $userId): array
    {
        $name = (string) (User::query()->whereKey($userId)->value('name') ?? '');
        $name = trim($name);

        if ($name === '') {
            return ['name' => '', 'short_name' => ''];
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        $shortName = $parts === [] ? $name : (string) end($parts);

        return ['name' => $name, 'short_name' => $shortName];
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
