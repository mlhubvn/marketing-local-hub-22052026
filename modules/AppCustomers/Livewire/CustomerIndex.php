<?php

namespace Modules\AppCustomers\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppCustomers\Models\Customer;

#[Title('Customers')]
class CustomerIndex extends Component
{
    use WithPagination;

    public ?LocalBusiness $scopedBusiness = null;
    public string $business_id = '';
    public string $name = '';
    public string $phone = '';
    public string $email = '';
    public string $note = '';
    public string $businessFilter = 'all';
    public string $search = '';
    public int $perPage = 10;
    public ?int $editingId = null;
    public ?string $statusMessage = null;

    public function mount(?LocalBusiness $business = null): void
    {
        if ($business) {
            abort_unless((int) $business->user_id === (int) auth()->id(), 404);

            $this->scopedBusiness = $business;
            $this->business_id = (string) $business->id;
            $this->businessFilter = (string) $business->id;
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedBusinessFilter(): void
    {
        if ($this->scopedBusiness) {
            $this->businessFilter = (string) $this->scopedBusiness->id;
        }

        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->perPage = (int) $this->perPage;

        if (! in_array($this->perPage, [10, 25, 50], true)) {
            $this->perPage = 10;
        }

        $this->resetPage();
    }

    public function create(): void
    {
        $this->resetForm();

        if ($this->scopedBusiness) {
            $this->business_id = (string) $this->scopedBusiness->id;
        }
    }

    public function edit(int $id): void
    {
        $customer = Customer::query()->where('user_id', auth()->id())->findOrFail($id);

        if ($this->scopedBusiness) {
            abort_unless((int) $customer->business_id === (int) $this->scopedBusiness->id, 404);
        }

        $this->editingId = $customer->id;
        $this->business_id = (string) ($customer->business_id ?: '');
        $this->name = (string) $customer->name;
        $this->phone = (string) ($customer->phone ?: '');
        $this->email = (string) ($customer->email ?: '');
        $this->note = (string) ($customer->note ?: '');
        $this->resetValidation();
    }

    public function save(): void
    {
        $payload = $this->validate([
            'business_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:80'],
            'email' => ['nullable', 'email', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($payload['business_id']) {
            LocalBusiness::query()->where('user_id', auth()->id())->findOrFail((int) $payload['business_id']);
        }

        if ($this->scopedBusiness) {
            $payload['business_id'] = $this->scopedBusiness->id;
        }

        $data = [
            'user_id' => auth()->id(),
            'business_id' => $payload['business_id'] ?: null,
            ...collect($payload)->except('business_id')->all(),
        ];

        if ($this->editingId) {
            Customer::query()->where('user_id', auth()->id())->whereKey($this->editingId)->update($data);
            $this->statusMessage = __('Customer updated.');
        } else {
            Customer::query()->create($data);
            $this->statusMessage = __('Customer saved.');
        }

        $this->resetForm();
        $this->resetPage();
        $this->dispatch('customer-saved');
    }

    public function delete(int $id): void
    {
        Customer::query()
            ->where('user_id', auth()->id())
            ->when($this->scopedBusiness, fn ($query) => $query->where('business_id', $this->scopedBusiness->id))
            ->whereKey($id)
            ->delete();
        $this->statusMessage = __('Customer deleted.');
        $this->resetPage();
    }

    public function render(): View
    {
        $businesses = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get();

        if ($this->scopedBusiness) {
            $this->businessFilter = (string) $this->scopedBusiness->id;
            $this->business_id = (string) $this->scopedBusiness->id;
        }

        if ($this->businessFilter !== 'all' && ! $businesses->contains('id', (int) $this->businessFilter)) {
            $this->businessFilter = 'all';
        }

        $customerQuery = Customer::query()
            ->where('user_id', auth()->id())
            ->when($this->businessFilter !== 'all', fn ($query) => $query->where('business_id', (int) $this->businessFilter))
            ->when(trim($this->search) !== '', function ($query): void {
                $search = trim($this->search);
                $query->where(function ($builder) use ($search): void {
                    $builder->where('name', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('note', 'like', '%'.$search.'%')
                        ->orWhereHas('business', fn ($businessQuery) => $businessQuery->where('name', 'like', '%'.$search.'%'));
                });
            })
            ->with('business')
            ->latest();

        return view('appcustomers::index', [
            'businesses' => $businesses,
            'customers' => $customerQuery->paginate($this->perPage),
            'totalCustomers' => Customer::query()
                ->where('user_id', auth()->id())
                ->when($this->scopedBusiness, fn ($query) => $query->where('business_id', $this->scopedBusiness->id))
                ->count(),
            'scopedBusiness' => $this->scopedBusiness,
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => $this->scopedBusiness ? __('Customers').' - '.$this->scopedBusiness->name : __('Customers'),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->business_id = '';
        $this->name = '';
        $this->phone = '';
        $this->email = '';
        $this->note = '';
        if ($this->scopedBusiness) {
            $this->business_id = (string) $this->scopedBusiness->id;
        }
        $this->resetValidation();
    }
}
