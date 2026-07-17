<?php

namespace Modules\APIPartnerFizaHUB\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Services\OnboardingAdminService;
use Modules\APIPartnerFizaHUB\Support\OnboardingStatusMachine;
use Throwable;

#[Title('FizaHUB onboarding')]
class FizaHubOnboardingIndex extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: 'all')]
    public string $statusFilter = 'all';

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'statusFilter']);
        $this->resetPage();
    }

    public function markContacted(int $id): void
    {
        $this->runAction($id, fn (PartnerOnboardingRequest $row) => app(OnboardingAdminService::class)
            ->markContacted($row, auth()->id()), __('Marked as contacted by consultant.'));
    }

    public function startConfiguring(int $id): void
    {
        $this->runAction($id, fn (PartnerOnboardingRequest $row) => app(OnboardingAdminService::class)
            ->startConfiguring($row, auth()->id()), __('Configuration started.'));
    }

    public function markReady(int $id): void
    {
        $this->runAction($id, fn (PartnerOnboardingRequest $row) => app(OnboardingAdminService::class)
            ->markReady($row, auth()->id()), __('Account marked as ready.'));
    }

    public function markCompleted(int $id): void
    {
        $this->runAction($id, fn (PartnerOnboardingRequest $row) => app(OnboardingAdminService::class)
            ->markCompleted($row, auth()->id()), __('Onboarding completed.'));
    }

    public function cancel(int $id): void
    {
        $this->runAction($id, fn (PartnerOnboardingRequest $row) => app(OnboardingAdminService::class)
            ->cancel($row, __('Cancelled by admin.'), auth()->id()), __('Onboarding cancelled.'));
    }

    public function resendWebhook(int $id): void
    {
        $this->runAction($id, function (PartnerOnboardingRequest $row): void {
            app(OnboardingAdminService::class)->resendWebhook($row);
        }, __('Webhook re-queued for delivery.'));
    }

    public function render(): View
    {
        $requests = $this->baseQuery()->paginate(15);

        $counts = PartnerOnboardingRequest::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return view('apipartnerfizahub::livewire.onboarding-index', [
            'requests' => $requests,
            'counts' => $counts,
            'totalCount' => array_sum($counts),
            'statusOptions' => OnboardingStatusMachine::labels(),
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('FizaHUB onboarding'),
        ]);
    }

    protected function baseQuery()
    {
        return PartnerOnboardingRequest::query()
            ->with(['user', 'business', 'consultant'])
            ->when($this->search !== '', function ($builder): void {
                $search = trim($this->search);

                $builder->where(function ($nested) use ($search): void {
                    $nested->where('external_business_id', 'like', "%{$search}%")
                        ->orWhere('request_id', 'like', "%{$search}%");
                });
            })
            ->when($this->statusFilter !== 'all', fn ($builder) => $builder->where('status', $this->statusFilter))
            ->orderByDesc('id');
    }

    protected function runAction(int $id, callable $callback, string $successMessage): void
    {
        $this->statusMessage = null;
        $this->errorMessage = null;

        $row = PartnerOnboardingRequest::query()->find($id);

        if (! $row) {
            $this->errorMessage = __('Onboarding request was not found.');

            return;
        }

        try {
            $callback($row);
            $this->statusMessage = $successMessage;
        } catch (Throwable $exception) {
            $this->errorMessage = $exception->getMessage();
        }
    }
}
