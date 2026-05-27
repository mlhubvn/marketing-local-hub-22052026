<?php

namespace Modules\AppAdvancedCustomerCrm\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTask;

#[Title('CRM Tasks')]
class CrmTasksIndex extends Component
{
    use WithPagination;

    public string $statusFilter = 'open';
    public string $priorityFilter = 'all';
    public int $perPage = 10;

    public function mount(): void
    {
        abort_unless(! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('advanced_crm') ?? false), 403);
    }

    public function completeTask(int $id): void
    {
        CustomerTask::query()
            ->whereHas('customer', fn ($query) => $query->where('user_id', auth()->id()))
            ->whereKey($id)
            ->update(['status' => 'done', 'completed_at' => now()]);
    }

    public function render(): View
    {
        return view('appadvancedcustomercrm::tasks-index', [
            'tasks' => CustomerTask::query()
                ->with(['customer.business'])
                ->whereHas('customer', fn ($query) => $query->where('user_id', auth()->id()))
                ->when($this->statusFilter !== 'all', fn ($query) => $query->where('status', $this->statusFilter))
                ->when($this->priorityFilter !== 'all', fn ($query) => $query->where('priority', $this->priorityFilter))
                ->latest('due_at')
                ->paginate($this->perPage),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('CRM Tasks')]);
    }
}
