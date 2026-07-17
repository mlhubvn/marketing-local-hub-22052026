<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\AppBookingPages\Models\Booking;
use Modules\AppCouponCampaigns\Models\CouponRedemption;
use Modules\AppFeedbackForms\Models\FeedbackResponse;
use Modules\AppLeadForms\Models\LeadSubmission;
use Modules\AppQRCampaigns\Models\QrCampaign;
use Modules\AppQRCampaigns\Models\QrScan;
use Modules\AppReviewBooster\Models\ReviewFeedback;

class DashboardService
{
    public function __construct(
        protected SupportTicketBridge $integrations
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function summarize(
        PartnerIntegration $integration,
        CarbonImmutable $from,
        CarbonImmutable $to
    ): array {
        $timezone = (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
        $fromStart = $from->timezone($timezone)->startOfDay();
        $toEnd = $to->timezone($timezone)->endOfDay();

        $businessId = (int) $integration->mlhub_business_id;
        $userId = (int) $integration->mlhub_user_id;

        $campaigns = QrCampaign::query()
            ->where('user_id', $userId)
            ->where('business_id', $businessId)
            ->get(['id', 'name', 'status', 'published_at', 'created_at']);

        $campaignIds = $campaigns->pluck('id');

        $qrScans = $this->countInRange(QrScan::query(), $userId, $campaignIds, $fromStart, $toEnd);
        $newLeads = $this->countInRange(LeadSubmission::query(), $userId, $campaignIds, $fromStart, $toEnd);
        // MVP: internal ReviewFeedback with rating >= 4 — not Google Reviews.
        $newReviews = $this->countInRange(
            ReviewFeedback::query()->where('rating', '>=', 4),
            $userId,
            $campaignIds,
            $fromStart,
            $toEnd
        );
        $couponClaims = $this->countInRange(CouponRedemption::query(), $userId, $campaignIds, $fromStart, $toEnd);
        $couponUsed = $this->countInRange(
            CouponRedemption::query()->whereNotNull('used_at'),
            $userId,
            $campaignIds,
            $fromStart,
            $toEnd,
            'used_at'
        );
        $bookings = $this->countInRange(Booking::query(), $userId, $campaignIds, $fromStart, $toEnd);
        $feedbackForms = $this->countInRange(FeedbackResponse::query(), $userId, $campaignIds, $fromStart, $toEnd);
        $lowReviews = $this->countInRange(
            ReviewFeedback::query()->where('rating', '<=', 3),
            $userId,
            $campaignIds,
            $fromStart,
            $toEnd
        );
        $feedback = $feedbackForms + $lowReviews;

        $conversions = $newLeads + $newReviews + $couponClaims + $bookings + $feedback;
        $conversionRate = $qrScans > 0
            ? round(($conversions / $qrScans) * 100, 2)
            : 0.0;

        $metrics = [
            'businesses' => $businessId > 0 ? 1 : 0,
            'campaigns' => $campaigns->count(),
            'active_campaigns' => $campaigns->whereNotNull('published_at')->count(),
            'qr_scans' => $qrScans,
            'new_leads' => $newLeads,
            'new_reviews' => $newReviews,
            'coupon_claims' => $couponClaims,
            'coupon_used' => $couponUsed,
            'bookings' => $bookings,
            'feedback' => $feedback,
            // Estimated metric: phone/email identities with >= 2 events in the period.
            'returning_customers' => $this->estimateReturningCustomers($userId, $campaignIds, $fromStart, $toEnd),
            'conversion_rate' => $conversionRate,
        ];

        return [
            'period' => [
                'from' => $fromStart->toDateString(),
                'to' => $to->timezone($timezone)->toDateString(),
                'timezone' => $timezone,
            ],
            'metrics' => $metrics,
            'campaigns' => $this->campaignRows($userId, $campaigns, $fromStart, $toEnd),
            'trend' => $this->dailyTrend($userId, $campaignIds, $fromStart, $to),
            'insights' => $this->insights($metrics),
            'suggested_actions' => $this->suggestedActions($metrics, $feedbackForms + $lowReviews),
        ];
    }

    /**
     * Cached dashboard summary with freshness metadata.
     *
     * @return array<string, mixed>
     */
    public function summarizeCached(
        PartnerIntegration $integration,
        CarbonImmutable $from,
        CarbonImmutable $to,
        bool $forceRefresh = false
    ): array {
        $timezone = (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
        $ttlMinutes = max(1, (int) config('modules.apipartnerfizahub.dashboard_cache_ttl_minutes', 45));
        $cooldown = max(0, (int) config('modules.apipartnerfizahub.dashboard_force_refresh_cooldown_seconds', 300));

        $key = $this->cacheKey($integration, $from, $to);
        $metaKey = $key.':generated_at';

        if ($forceRefresh) {
            // Force refresh is only honoured once per cooldown window to protect the app.
            $cooldownFree = Cache::add($key.':cooldown', now()->toIso8601String(), $cooldown);

            if ($cooldownFree) {
                $data = $this->summarize($integration, $from, $to);
                $generatedAt = now();
                Cache::put($key, $data, now()->addMinutes($ttlMinutes));
                Cache::put($metaKey, $generatedAt->toIso8601String(), now()->addMinutes($ttlMinutes));

                return $this->decorateFreshness($data, 'live', $timezone, $generatedAt, $ttlMinutes);
            }
        }

        $freshness = Cache::has($key) ? 'cached' : 'live';

        $data = Cache::remember($key, now()->addMinutes($ttlMinutes), function () use ($integration, $from, $to, $metaKey, $ttlMinutes): array {
            Cache::put($metaKey, now()->toIso8601String(), now()->addMinutes($ttlMinutes));

            return $this->summarize($integration, $from, $to);
        });

        $generatedAtIso = (string) (Cache::get($metaKey) ?: now()->toIso8601String());
        $generatedAt = CarbonImmutable::parse($generatedAtIso);

        return $this->decorateFreshness($data, $freshness, $timezone, $generatedAt, $ttlMinutes);
    }

    /**
     * @return array<string, mixed>
     */
    public function insightsPayload(
        PartnerIntegration $integration,
        CarbonImmutable $from,
        CarbonImmutable $to
    ): array {
        $summary = $this->summarize($integration, $from, $to);

        return [
            'period' => $summary['period'],
            'metrics' => $summary['metrics'],
            'insights' => $summary['insights'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function recommendationsPayload(
        PartnerIntegration $integration,
        CarbonImmutable $from,
        CarbonImmutable $to
    ): array {
        $summary = $this->summarize($integration, $from, $to);

        return [
            'period' => $summary['period'],
            'suggested_actions' => $summary['suggested_actions'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function campaignList(
        PartnerIntegration $integration,
        CarbonImmutable $from,
        CarbonImmutable $to
    ): array {
        $timezone = (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
        $fromStart = $from->timezone($timezone)->startOfDay();
        $toEnd = $to->timezone($timezone)->endOfDay();

        $userId = (int) $integration->mlhub_user_id;
        $businessId = (int) $integration->mlhub_business_id;

        $campaigns = QrCampaign::query()
            ->where('user_id', $userId)
            ->where('business_id', $businessId)
            ->orderByDesc('id')
            ->get(['id', 'name', 'status', 'published_at', 'created_at']);

        return [
            'period' => [
                'from' => $fromStart->toDateString(),
                'to' => $to->timezone($timezone)->toDateString(),
                'timezone' => $timezone,
            ],
            'items' => $this->campaignRows($userId, $campaigns, $fromStart, $toEnd, null),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function campaignDetail(
        PartnerIntegration $integration,
        int|string $campaignId,
        CarbonImmutable $from,
        CarbonImmutable $to
    ): array {
        $timezone = (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
        $fromStart = $from->timezone($timezone)->startOfDay();
        $toEnd = $to->timezone($timezone)->endOfDay();

        $userId = (int) $integration->mlhub_user_id;
        $businessId = (int) $integration->mlhub_business_id;

        $campaign = QrCampaign::query()
            ->where('user_id', $userId)
            ->where('business_id', $businessId)
            ->whereKey($campaignId)
            ->first();

        if (! $campaign) {
            throw (new ModelNotFoundException)->setModel(QrCampaign::class, [$campaignId]);
        }

        $campaignIds = collect([$campaign->id]);

        $scans = $this->countInRange(QrScan::query(), $userId, $campaignIds, $fromStart, $toEnd);
        $leads = $this->countInRange(LeadSubmission::query(), $userId, $campaignIds, $fromStart, $toEnd);
        $reviews = $this->countInRange(ReviewFeedback::query()->where('rating', '>=', 4), $userId, $campaignIds, $fromStart, $toEnd);
        $coupons = $this->countInRange(CouponRedemption::query(), $userId, $campaignIds, $fromStart, $toEnd);
        $bookings = $this->countInRange(Booking::query(), $userId, $campaignIds, $fromStart, $toEnd);
        $feedback = $this->countInRange(FeedbackResponse::query(), $userId, $campaignIds, $fromStart, $toEnd)
            + $this->countInRange(ReviewFeedback::query()->where('rating', '<=', 3), $userId, $campaignIds, $fromStart, $toEnd);

        $conversions = $leads + $reviews + $coupons + $bookings + $feedback;

        return [
            'period' => [
                'from' => $fromStart->toDateString(),
                'to' => $to->timezone($timezone)->toDateString(),
                'timezone' => $timezone,
            ],
            'campaign' => [
                'campaign_id' => $campaign->id,
                'name' => $campaign->name,
                'status' => $campaign->published_at ? 'active' : 'paused',
                'published_at' => optional($campaign->published_at)?->utc()?->toIso8601String(),
                'created_at' => optional($campaign->created_at)?->utc()?->toIso8601String(),
            ],
            'metrics' => [
                'scans' => $scans,
                'leads' => $leads,
                'reviews' => $reviews,
                'coupons' => $coupons,
                'bookings' => $bookings,
                'feedback' => $feedback,
                'conversions' => $conversions,
                'conversion_rate' => $scans > 0 ? round(($conversions / $scans) * 100, 2) : 0.0,
            ],
            'trend' => $this->dailyTrend($userId, $campaignIds, $fromStart, $to),
        ];
    }

    private function cacheKey(
        PartnerIntegration $integration,
        CarbonImmutable $from,
        CarbonImmutable $to
    ): string {
        $timezone = (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');

        return implode('|', [
            'fizahub:dashboard',
            $integration->partner_code,
            $integration->external_business_id,
            $from->timezone($timezone)->toDateString(),
            $to->timezone($timezone)->toDateString(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function decorateFreshness(
        array $data,
        string $freshness,
        string $timezone,
        CarbonImmutable $generatedAt,
        int $ttlMinutes
    ): array {
        $data['generated_at'] = $generatedAt->utc()->toIso8601String();
        $data['data_freshness'] = $freshness;
        $data['next_refresh_at'] = $generatedAt->addMinutes($ttlMinutes)->utc()->toIso8601String();
        $data['timezone'] = $timezone;

        return $data;
    }

    /**
     * @param  Builder<Model>  $query
     * @param  Collection<int, int|string>  $campaignIds
     */
    private function countInRange(
        $query,
        int $userId,
        Collection $campaignIds,
        CarbonImmutable $fromStart,
        CarbonImmutable $toEnd,
        string $dateColumn = 'created_at'
    ): int {
        if ($campaignIds->isEmpty()) {
            return 0;
        }

        return (int) $query
            ->where('user_id', $userId)
            ->whereIn('campaign_id', $campaignIds)
            ->whereBetween($dateColumn, [$fromStart, $toEnd])
            ->count();
    }

    /**
     * @param  Collection<int, int|string>  $campaignIds
     */
    private function estimateReturningCustomers(
        int $userId,
        Collection $campaignIds,
        CarbonImmutable $fromStart,
        CarbonImmutable $toEnd
    ): int {
        if ($campaignIds->isEmpty()) {
            return 0;
        }

        $identityCounts = [];

        $rows = [];

        foreach (
            LeadSubmission::query()
                ->where('user_id', $userId)
                ->whereIn('campaign_id', $campaignIds)
                ->whereBetween('created_at', [$fromStart, $toEnd])
                ->get(['phone', 'email']) as $row
        ) {
            $rows[] = ['phone' => $row->phone, 'email' => $row->email];
        }

        foreach (
            [
                Booking::query(),
                CouponRedemption::query(),
                ReviewFeedback::query(),
                FeedbackResponse::query(),
            ] as $builder
        ) {
            foreach (
                $builder
                    ->where('user_id', $userId)
                    ->whereIn('campaign_id', $campaignIds)
                    ->whereBetween('created_at', [$fromStart, $toEnd])
                    ->get(['customer_phone', 'customer_email']) as $row
            ) {
                $rows[] = [
                    'phone' => $row->customer_phone,
                    'email' => $row->customer_email,
                ];
            }
        }

        foreach ($rows as $row) {
            foreach ($this->identityKeys($row['phone'] ?? null, $row['email'] ?? null) as $key) {
                $identityCounts[$key] = ($identityCounts[$key] ?? 0) + 1;
            }
        }

        return count(array_filter($identityCounts, fn (int $count): bool => $count >= 2));
    }

    /**
     * @return list<string>
     */
    private function identityKeys(?string $phone, ?string $email): array
    {
        $keys = [];
        $phone = preg_replace('/\D+/', '', (string) $phone) ?: '';
        $email = strtolower(trim((string) $email));

        if ($phone !== '') {
            $keys[] = 'phone:'.$phone;
        }

        if ($email !== '') {
            $keys[] = 'email:'.$email;
        }

        return $keys;
    }

    /**
     * @param  Collection<int, QrCampaign>  $campaigns
     * @return list<array<string, mixed>>
     */
    private function campaignRows(
        int $userId,
        Collection $campaigns,
        CarbonImmutable $fromStart,
        CarbonImmutable $toEnd,
        ?int $limit = 10
    ): array {
        $rows = $campaigns->map(function (QrCampaign $campaign) use ($userId, $fromStart, $toEnd): array {
            $scans = (int) QrScan::query()
                ->where('user_id', $userId)
                ->where('campaign_id', $campaign->id)
                ->whereBetween('created_at', [$fromStart, $toEnd])
                ->count();

            $leads = (int) LeadSubmission::query()
                ->where('user_id', $userId)
                ->where('campaign_id', $campaign->id)
                ->whereBetween('created_at', [$fromStart, $toEnd])
                ->count();

            $reviews = (int) ReviewFeedback::query()
                ->where('user_id', $userId)
                ->where('campaign_id', $campaign->id)
                ->where('rating', '>=', 4)
                ->whereBetween('created_at', [$fromStart, $toEnd])
                ->count();

            $coupons = (int) CouponRedemption::query()
                ->where('user_id', $userId)
                ->where('campaign_id', $campaign->id)
                ->whereBetween('created_at', [$fromStart, $toEnd])
                ->count();

            $bookings = (int) Booking::query()
                ->where('user_id', $userId)
                ->where('campaign_id', $campaign->id)
                ->whereBetween('created_at', [$fromStart, $toEnd])
                ->count();

            $feedback = (int) FeedbackResponse::query()
                ->where('user_id', $userId)
                ->where('campaign_id', $campaign->id)
                ->whereBetween('created_at', [$fromStart, $toEnd])
                ->count()
                + (int) ReviewFeedback::query()
                    ->where('user_id', $userId)
                    ->where('campaign_id', $campaign->id)
                    ->where('rating', '<=', 3)
                    ->whereBetween('created_at', [$fromStart, $toEnd])
                    ->count();

            $conversions = $leads + $reviews + $coupons + $bookings + $feedback;

            return [
                'campaign_id' => $campaign->id,
                'name' => $campaign->name,
                'status' => $campaign->published_at ? 'active' : 'paused',
                'scans' => $scans,
                'conversions' => $conversions,
            ];
        })->sort(function (array $left, array $right): int {
            return [$right['conversions'], $right['scans']] <=> [$left['conversions'], $left['scans']];
        });

        if ($limit !== null) {
            $rows = $rows->take($limit);
        }

        return $rows->values()->all();
    }

    /**
     * @param  Collection<int, int|string>  $campaignIds
     * @return list<array<string, mixed>>
     */
    private function dailyTrend(
        int $userId,
        Collection $campaignIds,
        CarbonImmutable $fromStart,
        CarbonImmutable $to
    ): array {
        $timezone = (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
        $days = [];
        $cursor = $fromStart->timezone($timezone)->startOfDay();
        $end = $to->timezone($timezone)->startOfDay();

        while ($cursor->lessThanOrEqualTo($end)) {
            $days[$cursor->toDateString()] = [
                'date' => $cursor->toDateString(),
                'scans' => 0,
                'leads' => 0,
                'reviews' => 0,
                'coupons' => 0,
                'bookings' => 0,
                'feedback' => 0,
            ];
            $cursor = $cursor->addDay();
        }

        if ($campaignIds->isEmpty()) {
            return array_values($days);
        }

        $this->fillTrendCounts($days, QrScan::query(), $userId, $campaignIds, $fromStart, $end->endOfDay(), 'scans');
        $this->fillTrendCounts($days, LeadSubmission::query(), $userId, $campaignIds, $fromStart, $end->endOfDay(), 'leads');
        $this->fillTrendCounts(
            $days,
            ReviewFeedback::query()->where('rating', '>=', 4),
            $userId,
            $campaignIds,
            $fromStart,
            $end->endOfDay(),
            'reviews'
        );
        $this->fillTrendCounts($days, CouponRedemption::query(), $userId, $campaignIds, $fromStart, $end->endOfDay(), 'coupons');
        $this->fillTrendCounts($days, Booking::query(), $userId, $campaignIds, $fromStart, $end->endOfDay(), 'bookings');

        $feedbackDays = [];
        $this->fillTrendCounts(
            $feedbackDays,
            FeedbackResponse::query(),
            $userId,
            $campaignIds,
            $fromStart,
            $end->endOfDay(),
            'feedback'
        );
        $this->fillTrendCounts(
            $feedbackDays,
            ReviewFeedback::query()->where('rating', '<=', 3),
            $userId,
            $campaignIds,
            $fromStart,
            $end->endOfDay(),
            'feedback'
        );

        foreach ($feedbackDays as $date => $row) {
            if (isset($days[$date])) {
                $days[$date]['feedback'] += (int) ($row['feedback'] ?? 0);
            }
        }

        return array_values($days);
    }

    /**
     * @param  array<string, array<string, mixed>>  $days
     * @param  Builder<Model>  $query
     * @param  Collection<int, int|string>  $campaignIds
     */
    private function fillTrendCounts(
        array &$days,
        $query,
        int $userId,
        Collection $campaignIds,
        CarbonImmutable $fromStart,
        CarbonImmutable $toEnd,
        string $metric
    ): void {
        $rows = $query
            ->where('user_id', $userId)
            ->whereIn('campaign_id', $campaignIds)
            ->whereBetween('created_at', [$fromStart, $toEnd])
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get();

        foreach ($rows as $row) {
            $day = (string) $row->day;
            if (! isset($days[$day])) {
                $days[$day] = [
                    'date' => $day,
                    'scans' => 0,
                    'leads' => 0,
                    'reviews' => 0,
                    'coupons' => 0,
                    'bookings' => 0,
                    'feedback' => 0,
                ];
            }
            $days[$day][$metric] = (int) ($days[$day][$metric] ?? 0) + (int) $row->total;
        }
    }

    /**
     * @param  array<string, int|float>  $metrics
     * @return list<array{code: string, message: string}>
     */
    private function insights(array $metrics): array
    {
        return [
            [
                'code' => 'period_scans',
                'message' => 'QR scans in this period: '.(int) $metrics['qr_scans'],
            ],
            [
                'code' => 'period_leads',
                'message' => 'New leads in this period: '.(int) $metrics['new_leads'],
            ],
            [
                'code' => 'period_conversion',
                'message' => 'Conversion rate in this period: '.$metrics['conversion_rate'].'%',
            ],
        ];
    }

    /**
     * @param  array<string, int|float>  $metrics
     * @return list<array{code: string, message: string}>
     */
    private function suggestedActions(array $metrics, int $lowFeedbackTotal): array
    {
        $actions = [];

        if ((int) $metrics['campaigns'] === 0) {
            $actions[] = [
                'code' => 'create_campaign',
                'message' => 'Create your first campaign to start collecting scans and leads.',
            ];
        }

        if ((int) $metrics['campaigns'] > 0 && (int) $metrics['active_campaigns'] === 0) {
            $actions[] = [
                'code' => 'publish_campaign',
                'message' => 'Publish a campaign so customers can scan your QR.',
            ];
        }

        if ((int) $metrics['active_campaigns'] > 0 && (int) $metrics['qr_scans'] === 0) {
            $actions[] = [
                'code' => 'share_qr',
                'message' => 'Share your QR code to generate the first scans.',
            ];
        }

        if ((int) $metrics['new_leads'] > 0) {
            $actions[] = [
                'code' => 'follow_up_leads',
                'message' => 'Follow up on new leads captured in this period.',
            ];
        }

        if ($lowFeedbackTotal > 0) {
            $actions[] = [
                'code' => 'reply_feedback',
                'message' => 'Reply to low-score feedback to protect reputation.',
            ];
        }

        if ((int) $metrics['qr_scans'] >= 20 && (float) $metrics['conversion_rate'] < 5.0) {
            $actions[] = [
                'code' => 'improve_conversion',
                'message' => 'Improve offer and CTA to raise conversion above 5%.',
            ];
        }

        return $actions;
    }
}
