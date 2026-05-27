<?php

namespace Modules\AppAdvancedCustomerCrm\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\AppAdvancedCustomerCrm\Models\CustomerSegment;
use Modules\AppAdvancedCustomerCrm\Support\CustomerSegmentService;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCustomers\Models\Customer;

#[Title('CRM Segments')]
class CrmSegmentsIndex extends Component
{
    public string $name = 'High score customers';
    public string $description = '';
    public string $business_id = '';
    public string $match = 'all';
    public array $rules = [
        ['field' => 'score', 'operator' => '>=', 'value' => '50'],
    ];
    public string $color = '#0f766e';
    public ?int $editingId = null;
    public ?string $statusMessage = null;

    public function mount(): void
    {
        abort_unless(! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('advanced_crm') ?? false), 403);
    }

    public function save(): void
    {
        $payload = $this->validate($this->rulesForValidation());

        if (! $this->editingId) {
            $this->ensureSegmentLimit();
        }

        $wasEditing = (bool) $this->editingId;

        CustomerSegment::query()->updateOrCreate([
            'id' => $this->editingId,
            'team_id' => auth()->id(),
        ], [
            'team_id' => auth()->id(),
            'business_id' => filled($payload['business_id']) ? (int) $payload['business_id'] : null,
            'name' => $payload['name'],
            'description' => $payload['description'],
            'filters' => [
                'match' => $payload['match'],
                'rules' => collect($payload['rules'])->map(fn ($rule) => [
                    'field' => $rule['field'],
                    'operator' => $rule['operator'],
                    'value' => $rule['value'] ?? null,
                ])->values()->all(),
            ],
            'is_dynamic' => true,
            'color' => $payload['color'],
            'created_by' => auth()->id(),
        ]);

        $this->resetForm();
        $this->statusMessage = $wasEditing ? __('Segment updated.') : __('Segment created.');
        $this->dispatch('crm-segment-saved');
    }

    public function create(): void
    {
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $segment = CustomerSegment::query()->where('team_id', auth()->id())->findOrFail($id);
        $this->editingId = $segment->id;
        $this->name = $segment->name;
        $this->description = (string) $segment->description;
        $this->business_id = $segment->business_id ? (string) $segment->business_id : '';
        $this->match = (string) data_get($segment->filters, 'match', 'all');
        $this->rules = (array) data_get($segment->filters, 'rules', [['field' => 'score', 'operator' => '>=', 'value' => '50']]);
        $this->color = $segment->color ?: '#0f766e';
    }

    public function duplicate(int $id): void
    {
        $this->ensureSegmentLimit();

        $segment = CustomerSegment::query()->where('team_id', auth()->id())->findOrFail($id);

        CustomerSegment::query()->create([
            'team_id' => auth()->id(),
            'business_id' => $segment->business_id,
            'name' => __('Copy of :name', ['name' => $segment->name]),
            'description' => $segment->description,
            'filters' => $segment->filters,
            'is_dynamic' => true,
            'color' => $segment->color,
            'created_by' => auth()->id(),
        ]);

        $this->statusMessage = __('Segment duplicated.');
    }

    public function addRule(): void
    {
        if (count($this->rules) >= 8) {
            return;
        }

        $this->rules[] = ['field' => 'status', 'operator' => '=', 'value' => 'active'];
    }

    public function removeRule(int $index): void
    {
        if (count($this->rules) <= 1) {
            return;
        }

        unset($this->rules[$index]);
        $this->rules = array_values($this->rules);
    }

    public function delete(int $id): void
    {
        CustomerSegment::query()->where('team_id', auth()->id())->whereKey($id)->delete();
        $this->statusMessage = __('Segment deleted.');
    }

    public function render(): View
    {
        $base = Customer::query()->where('user_id', auth()->id());
        $savedSegments = CustomerSegment::query()->where('team_id', auth()->id())->latest()->get();
        $segmentService = app(CustomerSegmentService::class);
        $previewSegment = new CustomerSegment([
            'business_id' => filled($this->business_id) ? (int) $this->business_id : null,
            'filters' => [
                'match' => $this->match,
                'rules' => $this->rules,
            ],
        ]);

        return view('appadvancedcustomercrm::segments-index', [
            'businesses' => LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get(),
            'previewCount' => $segmentService->query($previewSegment)->count(),
            'savedSegments' => $savedSegments->map(fn (CustomerSegment $segment) => [
                'id' => $segment->id,
                'name' => $segment->name,
                'description' => $segment->description,
                'color' => $segment->color ?: '#0f766e',
                'count' => $segmentService->query($segment)->count(),
            ]),
            'segments' => [
                ['key' => 'new', 'name' => __('New Customers'), 'description' => __('Created in the last 30 days'), 'count' => (clone $base)->where('created_at', '>=', now()->subDays(30))->count(), 'color' => '#2563eb'],
                ['key' => 'vip', 'name' => __('VIP Customers'), 'description' => __('Score 80 or tagged VIP'), 'count' => (clone $base)->where(fn ($query) => $query->where('status', 'vip')->orWhere('score', '>=', 80)->orWhereHas('crmTags', fn ($tagQuery) => $tagQuery->where('slug', 'vip')))->count(), 'color' => '#f59e0b'],
                ['key' => 'inactive_30', 'name' => __('Inactive 30 Days'), 'description' => __('No recent activity in 30 days'), 'count' => (clone $base)->where(fn ($query) => $query->whereNull('last_activity_at')->orWhere('last_activity_at', '<', now()->subDays(30)))->count(), 'color' => '#64748b'],
                ['key' => 'coupon_claimed_not_used', 'name' => __('Coupon Claimed Not Used'), 'description' => __('Claimed coupons without redemption'), 'count' => (clone $base)->where('total_coupon_claims', '>', 0)->where('total_coupon_used', 0)->count(), 'color' => '#7c3aed'],
                ['key' => 'needs_follow_up', 'name' => __('Needs Follow-up'), 'description' => __('Tagged or has open follow-up tasks'), 'count' => (clone $base)->where(fn ($query) => $query->whereHas('crmTags', fn ($tagQuery) => $tagQuery->where('slug', 'needs-follow-up'))->orWhereHas('tasks', fn ($taskQuery) => $taskQuery->whereNotIn('status', ['done', 'cancelled'])))->count(), 'color' => '#dc2626'],
                ['key' => 'loyal', 'name' => __('Loyal Customers'), 'description' => __('Score 50 or higher'), 'count' => (clone $base)->where('score', '>=', 50)->count(), 'color' => '#16a34a'],
                ['key' => 'referral', 'name' => __('Referral Customers'), 'description' => __('Customers with referral conversions'), 'count' => (clone $base)->where('total_referrals', '>', 0)->count(), 'color' => '#0891b2'],
            ],
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('CRM Segments')]);
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = 'High score customers';
        $this->description = '';
        $this->business_id = '';
        $this->match = 'all';
        $this->rules = [['field' => 'score', 'operator' => '>=', 'value' => '50']];
        $this->color = '#0f766e';
    }

    protected function rulesForValidation(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'business_id' => ['nullable', 'integer'],
            'match' => ['required', 'string', 'in:all,any'],
            'rules' => ['required', 'array', 'min:1', 'max:8'],
            'rules.*.field' => ['required', 'string', 'in:status,score,tag,business_id,source_type,open_tasks,total_bookings,total_coupon_claims,total_coupon_used,total_feedback,total_reviews,total_loyalty_stamps,total_referrals,last_activity_at,created_at,email,phone,name'],
            'rules.*.operator' => ['required', 'string', 'in:=,!=,>,<,>=,<=,contains,not_contains,within_days,older_than_days,before,after,exists,not_exists'],
            'rules.*.value' => ['nullable', 'string', 'max:255'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }

    protected function ensureSegmentLimit(): void
    {
        $limit = auth()->user()?->planLimit('customer_segments', -1);
        if ((int) $limit === -1) {
            return;
        }
        abort_if(CustomerSegment::query()->where('team_id', auth()->id())->count() >= (int) $limit, 403, __('Your CRM segment limit has been reached.'));
    }
}
