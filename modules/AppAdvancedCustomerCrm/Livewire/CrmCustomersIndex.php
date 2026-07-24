<?php

namespace Modules\AppAdvancedCustomerCrm\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppAdvancedCustomerCrm\Models\CustomerSegment;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTag;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTask;
use Modules\AppAdvancedCustomerCrm\Support\CustomerActivityService;
use Modules\AppAdvancedCustomerCrm\Support\CustomerSegmentService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCustomers\Models\Customer;

#[Title('CRM Customers')]
class CrmCustomersIndex extends Component
{
    use WithPagination;

    public string $search = '';

    #[Url(as: 'business')]
    public string $businessFilter = 'all';

    #[Url(as: 'status')]
    public string $statusFilter = 'all';

    #[Url(as: 'tag')]
    public string $tagFilter = 'all';

    #[Url(as: 'segment')]
    public string $segmentFilter = 'all';

    public int $perPage = 10;

    public ?string $statusMessage = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canUsePlanFeature('advanced_crm'), 403);
        $this->seedSystemTags();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBusinessFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTagFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSegmentFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->perPage = in_array((int) $this->perPage, [10, 25, 50], true) ? (int) $this->perPage : 10;
        $this->resetPage();
    }

    public function markVip(int $id): void
    {
        $customer = Customer::query()->where('user_id', auth()->id())->findOrFail($id);
        $customer->forceFill(['status' => 'vip'])->save();
        app(CustomerActivityService::class)->record($customer, 'status_changed', __('Customer marked VIP'));
        $this->statusMessage = __('Customer marked as VIP.');
    }

    public function render(): View
    {
        $businesses = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get();
        $tags = CustomerTag::query()->where('owner_user_id', auth()->id())->orderBy('name')->get();
        $segments = CustomerSegment::query()->where('owner_user_id', auth()->id())->orderBy('name')->get();

        $base = Customer::query()
            ->where('user_id', auth()->id())
            ->with(['business', 'crmTags'])
            ->with(['activities' => fn ($query) => $query->latest('occurred_at')->latest()->limit(1)])
            ->withCount(['tasks as open_tasks_count' => fn ($query) => $query->whereNotIn('status', ['done', 'cancelled'])])
            ->when($this->businessFilter !== 'all', fn ($query) => $query->where('business_id', (int) $this->businessFilter))
            ->when($this->statusFilter !== 'all', fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->tagFilter !== 'all', fn ($query) => $query->whereHas('crmTags', fn ($tagQuery) => $tagQuery->whereKey((int) $this->tagFilter)))
            ->when($this->segmentFilter !== 'all', function ($query): void {
                if (is_numeric($this->segmentFilter)) {
                    $segment = CustomerSegment::query()->where('owner_user_id', auth()->id())->find((int) $this->segmentFilter);
                    if ($segment) {
                        $query->whereIn('id', app(CustomerSegmentService::class)->query($segment)->pluck('id'));
                    }

                    return;
                }

                $this->applySystemSegment($query, $this->segmentFilter);
            })
            ->when(trim($this->search) !== '', function ($query): void {
                $search = trim($this->search);
                $query->where(function ($builder) use ($search): void {
                    $builder->where('name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhereHas('business', fn ($businessQuery) => $businessQuery->where('name', 'like', '%'.$search.'%'));
                });
            });

        $allCustomerIds = Customer::query()->where('user_id', auth()->id())->pluck('id');

        return view('appadvancedcustomercrm::customers-index', [
            'businesses' => $businesses,
            'tags' => $tags,
            'segments' => $segments,
            'systemSegments' => $this->systemSegments(),
            'customers' => (clone $base)->latest('last_activity_at')->latest()->paginate($this->perPage),
            'stats' => [
                'total' => $allCustomerIds->count(),
                'vip' => Customer::query()->whereIn('id', $allCustomerIds)->where('status', 'vip')->count(),
                'needs_follow_up' => CustomerTask::query()->whereIn('customer_id', $allCustomerIds)->whereNotIn('status', ['done', 'cancelled'])->count(),
                'inactive' => Customer::query()->whereIn('id', $allCustomerIds)->where(function ($query): void {
                    $query->whereNull('last_activity_at')->orWhere('last_activity_at', '<', now()->subDays(30));
                })->count(),
            ],
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('CRM Customers')]);
    }

    protected function applySystemSegment(Builder $query, string $segment): void
    {
        match ($segment) {
            'new' => $query->where('created_at', '>=', now()->subDays(30)),
            'vip' => $query->where(fn ($builder) => $builder
                ->where('status', 'vip')
                ->orWhere('score', '>=', 80)
                ->orWhereHas('crmTags', fn ($tagQuery) => $tagQuery->where('slug', 'vip'))),
            'inactive_30' => $query->where(fn ($builder) => $builder
                ->whereNull('last_activity_at')
                ->orWhere('last_activity_at', '<', now()->subDays(30))),
            'coupon_claimed_not_used' => $query->where('total_coupon_claims', '>', 0)->where('total_coupon_used', 0),
            'needs_follow_up' => $query->where(fn ($builder) => $builder
                ->whereHas('crmTags', fn ($tagQuery) => $tagQuery->where('slug', 'needs-follow-up'))
                ->orWhereHas('tasks', fn ($taskQuery) => $taskQuery->whereNotIn('status', ['done', 'cancelled']))),
            'loyal' => $query->where('score', '>=', 50),
            'referral' => $query->where('total_referrals', '>', 0),
            default => null,
        };
    }

    protected function systemSegments(): array
    {
        return [
            ['value' => 'new', 'label' => __('New Customers'), 'icon' => 'fa-chart-pie-simple'],
            ['value' => 'vip', 'label' => __('VIP Customers'), 'icon' => 'fa-crown'],
            ['value' => 'inactive_30', 'label' => __('Inactive 30 Days'), 'icon' => 'fa-user-clock'],
            ['value' => 'coupon_claimed_not_used', 'label' => __('Coupon Claimed Not Used'), 'icon' => 'fa-ticket'],
            ['value' => 'needs_follow_up', 'label' => __('Needs Follow-up'), 'icon' => 'fa-list-check'],
            ['value' => 'loyal', 'label' => __('Loyal Customers'), 'icon' => 'fa-heart'],
            ['value' => 'referral', 'label' => __('Referral Customers'), 'icon' => 'fa-share-nodes'],
        ];
    }

    protected function seedSystemTags(): void
    {
        foreach ([
            ['VIP', '#f59e0b'],
            ['New Customer', '#2563eb'],
            ['Returning Customer', '#0f766e'],
            ['Needs Follow-up', '#dc2626'],
            ['Coupon Claimed', '#7c3aed'],
            ['Low-score Feedback', '#ef4444'],
            ['Loyal Customer', '#16a34a'],
            ['Referral Customer', '#0891b2'],
            ['Inactive', '#64748b'],
        ] as [$name, $color]) {
            CustomerTag::query()->firstOrCreate(
                ['owner_user_id' => auth()->id(), 'slug' => str($name)->slug()->toString()],
                ['name' => $name, 'color' => $color, 'is_system' => true]
            );
        }
    }
}
