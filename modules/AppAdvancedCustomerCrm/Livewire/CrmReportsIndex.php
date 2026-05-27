<?php

namespace Modules\AppAdvancedCustomerCrm\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAdvancedCustomerCrm\Models\CustomerActivity;
use Modules\AppAdvancedCustomerCrm\Models\CustomerSegment;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTag;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTask;
use Modules\AppAdvancedCustomerCrm\Support\CustomerSegmentService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCustomers\Models\Customer;

#[Title('CRM Reports')]
class CrmReportsIndex extends Component
{
    public string $businessFilter = 'all';
    public string $dateRange = '12_months';
    public string $segmentFilter = 'all';
    public string $tagFilter = 'all';

    public function mount(): void
    {
        abort_unless(! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('advanced_crm') ?? false), 403);
    }

    public function render(): View
    {
        $customerQuery = Customer::query()->where('user_id', auth()->id());
        if ($this->businessFilter !== 'all') {
            $customerQuery->where('business_id', (int) $this->businessFilter);
        }
        if ($this->tagFilter !== 'all') {
            $customerQuery->whereHas('crmTags', fn ($query) => $query->whereKey((int) $this->tagFilter));
        }
        if ($this->segmentFilter !== 'all') {
            $segment = CustomerSegment::query()->where('team_id', auth()->id())->find((int) $this->segmentFilter);
            if ($segment) {
                $segmentIds = app(CustomerSegmentService::class)->query($segment)->pluck('id');
                $customerQuery->whereIn('id', $segmentIds);
            }
        }

        $startDate = match ($this->dateRange) {
            '30_days' => now()->subDays(30),
            '90_days' => now()->subDays(90),
            'year' => now()->startOfYear(),
            default => now()->subMonths(11)->startOfMonth(),
        };

        $customerIds = (clone $customerQuery)->pluck('id');
        $months = $this->dateRange === '30_days' ? 5 : ($this->dateRange === '90_days' ? 5 : 11);
        $maxGrowth = max(1, (clone $customerQuery)->where('created_at', '>=', $startDate)->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as total')->groupBy('month')->pluck('total')->max() ?: 1);
        $growth = collect(range($months, 0))->map(function (int $offset) use ($customerIds, $maxGrowth): array {
            $date = now()->subMonths($offset);
            $count = Customer::query()
                ->whereIn('id', $customerIds)
                ->whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            return ['label' => $date->format('M'), 'count' => $count, 'percent' => (int) round(($count / $maxGrowth) * 100)];
        });

        $activityRows = CustomerActivity::query()
            ->whereIn('customer_id', $customerIds)
            ->where('occurred_at', '>=', $startDate)
            ->selectRaw('type, COUNT(*) as total')
            ->groupBy('type')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $activityLabels = [
            'lead_submitted' => __('Lead Submitted'),
            'booking_submitted' => __('Booking Created'),
            'coupon_claimed' => __('Coupon Claimed'),
            'feedback_submitted' => __('Feedback Submitted'),
            'review_rating_submitted' => __('Review Submitted'),
            'google_review_synced' => __('Review Synced'),
            'task_completed' => __('Task Completed'),
            'note_created' => __('Note Added'),
        ];
        $activityCounts = collect($activityLabels)->map(fn ($label, $type) => (int) $activityRows->firstWhere('type', $type)?->total);

        $taskBase = CustomerTask::query()->whereIn('customer_id', $customerIds);
        $taskStatus = [
            __('Open') => (clone $taskBase)->where('status', 'open')->count(),
            __('In progress') => (clone $taskBase)->where('status', 'in_progress')->count(),
            __('Due today') => (clone $taskBase)->whereDate('due_at', today())->whereNotIn('status', ['done', 'cancelled'])->count(),
            __('Overdue') => (clone $taskBase)->where('due_at', '<', now())->whereNotIn('status', ['done', 'cancelled'])->count(),
            __('Completed') => (clone $taskBase)->where('status', 'done')->count(),
        ];

        $sourceRows = (clone $customerQuery)
            ->selectRaw('COALESCE(source_type, JSON_UNQUOTE(JSON_EXTRACT(metadata, "$.last_source")), "manual") as source, COUNT(*) as total')
            ->groupBy('source')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return view('appadvancedcustomercrm::reports-index', [
            'metrics' => [
                ['label' => __('Total Customers'), 'value' => $customerIds->count(), 'icon' => 'fa-light fa-users'],
                ['label' => __('VIP Customers'), 'value' => Customer::query()->whereIn('id', $customerIds)->where('status', 'vip')->count(), 'icon' => 'fa-light fa-crown'],
                ['label' => __('Open Tasks'), 'value' => CustomerTask::query()->whereIn('customer_id', $customerIds)->whereNotIn('status', ['done', 'cancelled'])->count(), 'icon' => 'fa-light fa-list-check'],
                ['label' => __('Average CRM Score'), 'value' => (int) Customer::query()->whereIn('id', $customerIds)->avg('score'), 'suffix' => '/100', 'icon' => 'fa-light fa-gauge-high'],
            ],
            'scoreBuckets' => [
                ['label' => __('New'), 'count' => Customer::query()->whereIn('id', $customerIds)->whereBetween('score', [0, 20])->count()],
                ['label' => __('Engaged'), 'count' => Customer::query()->whereIn('id', $customerIds)->whereBetween('score', [21, 50])->count()],
                ['label' => __('Loyal'), 'count' => Customer::query()->whereIn('id', $customerIds)->whereBetween('score', [51, 80])->count()],
                ['label' => __('VIP'), 'count' => Customer::query()->whereIn('id', $customerIds)->where('score', '>=', 81)->count()],
            ],
            'growth' => $growth,
            'growthCategories' => $growth->pluck('label')->values()->all(),
            'growthSeries' => [[
                'name' => __('New customers'),
                'data' => $growth->pluck('count')->values()->all(),
            ]],
            'activities' => $activityRows->map(fn ($row) => [
                'label' => str((string) $row->type)->replace('_', ' ')->headline()->toString(),
                'count' => (int) $row->total,
            ]),
            'activityCategories' => collect($activityLabels)->values()->all(),
            'activitySeries' => [[
                'name' => __('Activities'),
                'data' => $activityCounts->values()->all(),
            ]],
            'scoreSeries' => [[
                'name' => __('Customers'),
                'data' => collect([
                    ['name' => __('New'), 'y' => Customer::query()->whereIn('id', $customerIds)->whereBetween('score', [0, 20])->count()],
                    ['name' => __('Engaged'), 'y' => Customer::query()->whereIn('id', $customerIds)->whereBetween('score', [21, 50])->count()],
                    ['name' => __('Loyal'), 'y' => Customer::query()->whereIn('id', $customerIds)->whereBetween('score', [51, 80])->count()],
                    ['name' => __('VIP'), 'y' => Customer::query()->whereIn('id', $customerIds)->where('score', '>=', 81)->count()],
                ])->all(),
            ]],
            'tags' => CustomerTag::query()
                ->where('team_id', auth()->id())
                ->withCount('customers')
                ->orderByDesc('customers_count')
                ->limit(8)
                ->get(),
            'businesses' => LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get(),
            'segments' => CustomerSegment::query()->where('team_id', auth()->id())->orderBy('name')->get(),
            'allTags' => CustomerTag::query()->where('team_id', auth()->id())->orderBy('name')->get(),
            'taskCategories' => array_keys($taskStatus),
            'taskSeries' => [['name' => __('Tasks'), 'data' => array_values($taskStatus)]],
            'sourceSeries' => [[
                'name' => __('Customers'),
                'data' => $sourceRows->map(fn ($row) => ['name' => str((string) $row->source)->replace('_', ' ')->headline()->toString(), 'y' => (int) $row->total])->values()->all(),
            ]],
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('CRM Reports')]);
    }
}
