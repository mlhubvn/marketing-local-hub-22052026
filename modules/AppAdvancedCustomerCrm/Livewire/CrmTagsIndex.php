<?php

namespace Modules\AppAdvancedCustomerCrm\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTag;

#[Title('CRM Tags')]
class CrmTagsIndex extends Component
{
    use WithPagination;

    public string $name = '';
    public string $description = '';
    public string $color = '#0f766e';
    public int $perPage = 10;
    public ?int $editingId = null;
    public ?string $statusMessage = null;

    public function mount(): void
    {
        abort_unless(! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('advanced_crm') ?? false), 403);
        $this->seedSystemTags();
    }

    public function save(): void
    {
        $payload = $this->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:500'],
            'color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $data = [
            'team_id' => auth()->id(),
            'name' => $payload['name'],
            'slug' => str($payload['name'])->slug()->toString(),
            'description' => $payload['description'],
            'color' => $payload['color'],
            'is_system' => false,
        ];

        if ($this->editingId) {
            CustomerTag::query()->where('team_id', auth()->id())->whereKey($this->editingId)->where('is_system', false)->update($data);
            $this->statusMessage = __('Tag updated.');
        } else {
            $this->ensureTagLimit();
            CustomerTag::query()->updateOrCreate(
                ['team_id' => auth()->id(), 'slug' => $data['slug']],
                $data
            );
            $this->statusMessage = __('Tag created.');
        }

        $this->resetForm();
        $this->resetPage();
        $this->dispatch('crm-tag-saved');
    }

    public function edit(int $id): void
    {
        $tag = CustomerTag::query()->where('team_id', auth()->id())->whereKey($id)->firstOrFail();

        $this->editingId = $tag->id;
        $this->name = $tag->name;
        $this->description = (string) $tag->description;
        $this->color = $tag->color ?: '#0f766e';
    }

    public function delete(int $id): void
    {
        CustomerTag::query()->where('team_id', auth()->id())->whereKey($id)->where('is_system', false)->delete();
        $this->statusMessage = __('Tag deleted.');
    }

    public function updatedPerPage(): void
    {
        $this->perPage = in_array((int) $this->perPage, [10, 25, 50], true) ? (int) $this->perPage : 10;
        $this->resetPage();
    }

    public function render(): View
    {
        return view('appadvancedcustomercrm::tags-index', [
            'tags' => CustomerTag::query()
                ->where('team_id', auth()->id())
                ->withCount('customers')
                ->orderByDesc('is_system')
                ->orderBy('name')
                ->paginate($this->perPage),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('CRM Tags')]);
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->description = '';
        $this->color = '#0f766e';
    }

    protected function ensureTagLimit(): void
    {
        $limit = auth()->user()?->planLimit('customer_tags', -1);
        if ((int) $limit === -1) {
            return;
        }
        abort_if(CustomerTag::query()->where('team_id', auth()->id())->count() >= (int) $limit, 403, __('Your CRM tag limit has been reached.'));
    }

    protected function seedSystemTags(): void
    {
        foreach ([
            ['VIP', '#f59e0b'],
            ['New Customer', '#2563eb'],
            ['Returning Customer', '#16a34a'],
            ['Needs Follow-up', '#dc2626'],
            ['Coupon Claimed', '#7c3aed'],
            ['Coupon Used', '#0f766e'],
            ['Low-score Feedback', '#ef4444'],
            ['High Rating', '#22c55e'],
            ['Booking Customer', '#0891b2'],
            ['Loyal Customer', '#0f766e'],
            ['Referral Customer', '#8b5cf6'],
            ['Inactive', '#64748b'],
        ] as [$name, $color]) {
            CustomerTag::query()->firstOrCreate(
                ['team_id' => auth()->id(), 'slug' => str($name)->slug()->toString()],
                ['name' => $name, 'color' => $color, 'is_system' => true]
            );
        }
    }
}
