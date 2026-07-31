<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerPackageAssignment;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportTicketContext;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;

/**
 * Read-only aggregation service backing the FizaHUB Partner Reporting Portal (overview
 * totals, growth charts, business list, single-business detail).
 *
 * Every query is scoped to this module's own tables AND explicitly filtered to this
 * module's `partner_code` (see {@see PartnerMappingService::partnerCode()}). Today
 * `partner_onboarding_requests`/`partner_integrations` only ever hold FizaHUB rows, but the
 * explicit filter is kept as defense-in-depth so this portal can never surface another
 * partner's rows even if the schema is reused for a second partner in the future.
 *
 * Growth metrics and support-ticket data for a single business reuse the existing
 * {@see DashboardService} and {@see SupportTicketBridge} instead of recomputing formulas
 * that already live elsewhere in this module.
 */
class PartnerReportingService
{
    public function __construct(
        protected DashboardService $dashboard,
        protected SupportTicketBridge $ticketBridge,
        protected PartnerMappingService $mapping,
    ) {}

    /**
     * @return array{total: int, linked_businesses: int, completed: int, in_progress: int, cancelled: int, open_tickets: int, status_breakdown: list<array{code: string, label: string, total: int, percent: float}>}
     */
    public function overviewMetrics(): array
    {
        $statusCounts = $this->statusCounts();
        $total = array_sum($statusCounts);
        $completed = $statusCounts[OnboardingStatusMachine::COMPLETED] ?? 0;
        $cancelled = $statusCounts[OnboardingStatusMachine::CANCELLED] ?? 0;
        $labels = OnboardingStatusMachine::labels();

        $breakdown = [];
        foreach (OnboardingStatusMachine::PUBLIC_STATUSES as $code) {
            $count = $statusCounts[$code] ?? 0;
            $breakdown[] = [
                'code' => $code,
                'label' => $labels[$code] ?? $code,
                'total' => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
            ];
        }

        return [
            'total' => $total,
            'linked_businesses' => (int) PartnerIntegration::query()
                ->where('partner_code', $this->mapping->partnerCode())
                ->whereNotNull('mlhub_business_id')
                ->count(),
            'completed' => $completed,
            'in_progress' => max(0, $total - $completed - $cancelled),
            'cancelled' => $cancelled,
            'open_tickets' => $this->openTicketCount(),
            'status_breakdown' => $breakdown,
        ];
    }

    /**
     * @return list<array{date: string, label: string, value: int}>
     */
    public function dailyGrowth(int $days = 30): array
    {
        return $this->growthBuckets('day', $days);
    }

    /**
     * @return list<array{date: string, label: string, value: int}>
     */
    public function monthlyGrowth(int $months = 12): array
    {
        return $this->growthBuckets('month', $months);
    }

    /**
     * @return LengthAwarePaginator<int, PartnerOnboardingRequest>
     */
    public function businessList(string $search, string $statusFilter, string $packageFilter, int $perPage = 15): LengthAwarePaginator
    {
        return $this->businessListQuery($search, $statusFilter, $packageFilter)
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Distinct package codes actually present on onboarding requests, for the filter
     * dropdown — never a hard-coded list, so it can never drift from real data.
     *
     * @return list<string>
     */
    public function availablePackageCodes(): array
    {
        return PartnerOnboardingRequest::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->whereNotNull('package_code')
            ->where('package_code', '!=', '')
            ->distinct()
            ->orderBy('package_code')
            ->pluck('package_code')
            ->all();
    }

    public function findOnboardingOrFail(int $id): PartnerOnboardingRequest
    {
        return PartnerOnboardingRequest::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->with([
                'user:id,name,username,email,plan_id,plan_started_at,plan_expires_at',
                'user.plan:id,name,slug,status',
                'business:id,name,phone,email,address,website,user_id',
                'consultant:id,name,email',
                'statusHistories' => fn ($query) => $query->orderBy('created_at')->orderBy('id'),
            ])
            ->findOrFail($id);
    }

    public function findIntegrationFor(PartnerOnboardingRequest $onboarding): ?PartnerIntegration
    {
        if (blank($onboarding->external_business_id)) {
            return null;
        }

        return PartnerIntegration::query()
            ->where('partner_code', $onboarding->partner_code ?: $this->mapping->partnerCode())
            ->where('external_business_id', $onboarding->external_business_id)
            ->first();
    }

    /**
     * Growth metrics for a single HKD, reusing DashboardService as-is. Returns null when
     * the business/user mapping isn't provisioned yet — the view must render "Chưa có dữ
     * liệu" for that case rather than a zeroed-out fake chart.
     *
     * @return array<string, mixed>|null
     */
    public function growthSummaryFor(PartnerIntegration $integration): ?array
    {
        if (! $integration->mlhub_business_id || ! $integration->mlhub_user_id) {
            return null;
        }

        $timezone = (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
        $to = CarbonImmutable::now($timezone);
        $from = $to->subDays(29);

        return $this->dashboard->summarizeCached($integration, $from, $to);
    }

    /**
     * @return array<string, mixed>
     */
    public function supportTicketsFor(PartnerIntegration $integration): array
    {
        return $this->ticketBridge->list($integration, ['per_page' => 50]);
    }

    /**
     * Read-only ticket detail (subject + full message history) for the expandable ticket
     * panel. Delegates the tenant scoping (uid/team match) to SupportTicketBridge so a
     * ticket secure ID belonging to a different HKD can never be read this way.
     *
     * @return array<string, mixed>
     */
    public function ticketDetail(PartnerIntegration $integration, string $ticketSecureId): array
    {
        return $this->ticketBridge->detail($integration, $ticketSecureId);
    }

    /**
     * @return Collection<int, PartnerPackageAssignment>
     */
    public function packageHistoryFor(PartnerIntegration $integration): Collection
    {
        return PartnerPackageAssignment::query()
            ->with('plan:id,name,slug')
            ->where('partner_integration_id', $integration->id)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Only reads the actually-stored `plan_expires_at` timestamp — never inferred from
     * anything else, per the "no guessing plan status from uncertain data" requirement.
     */
    public function planWindowStatus(?CarbonInterface $expiresAt): string
    {
        if (! $expiresAt) {
            return 'unknown';
        }

        if ($expiresAt->isPast()) {
            return 'expired';
        }

        if (now()->diffInDays($expiresAt) <= 7) {
            return 'expiring_soon';
        }

        return 'active';
    }

    /**
     * @return array<string, int> keyed by OnboardingStatusMachine public status code
     */
    private function statusCounts(): array
    {
        $rawCounts = PartnerOnboardingRequest::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = array_fill_keys(OnboardingStatusMachine::PUBLIC_STATUSES, 0);

        foreach ($rawCounts as $rawStatus => $total) {
            $publicStatus = OnboardingStatusMachine::publicStatus((string) $rawStatus);
            $counts[$publicStatus] = ($counts[$publicStatus] ?? 0) + (int) $total;
        }

        return $counts;
    }

    private function openTicketCount(): int
    {
        if (! Schema::hasTable('partner_support_ticket_contexts')) {
            return 0;
        }

        return (int) SupportTicket::query()
            ->where('status', 1)
            ->whereIn('id', PartnerSupportTicketContext::query()->select('support_ticket_id'))
            ->count();
    }

    /**
     * @return list<array{date: string, label: string, value: int}>
     */
    private function growthBuckets(string $unit, int $count): array
    {
        $timezone = (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
        $now = CarbonImmutable::now($timezone);

        $end = $unit === 'day' ? $now->endOfDay() : $now->endOfMonth();
        $start = $unit === 'day'
            ? $end->subDays($count - 1)->startOfDay()
            : $end->subMonths($count - 1)->startOfMonth();

        $buckets = [];
        $cursor = $start;
        while ($cursor->lessThanOrEqualTo($end)) {
            $key = $unit === 'day' ? $cursor->toDateString() : $cursor->format('Y-m');
            $buckets[$key] = [
                'date' => $key,
                'label' => $unit === 'day' ? $cursor->format('d/m') : $cursor->format('m/Y'),
                'value' => 0,
            ];
            $cursor = $unit === 'day' ? $cursor->addDay() : $cursor->addMonth();
        }

        // Only the `created_at` column, only rows inside the bucketed window — never the
        // whole table — the PHP-side bucketing below just needs to convert each timestamp
        // to the app timezone (bucketing in SQL would need driver-specific TZ conversion).
        $createdTimestamps = PartnerOnboardingRequest::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->whereBetween('created_at', [$start->utc(), $end->utc()])
            ->pluck('created_at');

        foreach ($createdTimestamps as $createdAt) {
            $local = CarbonImmutable::parse($createdAt)->timezone($timezone);
            $key = $unit === 'day' ? $local->toDateString() : $local->format('Y-m');

            if (isset($buckets[$key])) {
                $buckets[$key]['value']++;
            }
        }

        return array_values($buckets);
    }

    private function businessListQuery(string $search, string $statusFilter, string $packageFilter): Builder
    {
        $search = trim($search);

        return PartnerOnboardingRequest::query()
            ->where('partner_code', $this->mapping->partnerCode())
            ->with([
                'user:id,name,username,email,plan_id',
                'user.plan:id,name,slug',
                'business:id,name,phone,email,address,user_id',
            ])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('external_business_id', 'like', "%{$search}%")
                        ->orWhere('request_id', 'like', "%{$search}%")
                        ->orWhereHas('business', function (Builder $business) use ($search): void {
                            $business->where('name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function (Builder $user) use ($search): void {
                            $user->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        });
                });
            })
            ->when($statusFilter !== 'all', function (Builder $query) use ($statusFilter): void {
                $query->whereIn('status', $this->rawStatusesForPublicStatus($statusFilter));
            })
            ->when($packageFilter !== 'all', fn (Builder $query) => $query->where('package_code', $packageFilter));
    }

    /**
     * @return list<string>
     */
    private function rawStatusesForPublicStatus(string $publicStatus): array
    {
        $matches = [$publicStatus];

        foreach (OnboardingStatusMachine::LEGACY_STATUSES as $legacy) {
            if (OnboardingStatusMachine::publicStatus($legacy) === $publicStatus) {
                $matches[] = $legacy;
            }
        }

        return array_values(array_unique($matches));
    }
}
