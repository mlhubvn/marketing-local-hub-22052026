<?php

namespace Modules\APIPartnerFizaHUB\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
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

    /** @var array<int|string, string> */
    public array $stageSelections = [];

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

    public function applyStage(int $id): void
    {
        $toStatus = trim((string) ($this->stageSelections[$id] ?? ''));

        if ($toStatus === '') {
            $this->statusMessage = null;
            $this->errorMessage = __('Please choose a stage.');

            return;
        }

        $this->runAction($id, function (PartnerOnboardingRequest $row) use ($toStatus): void {
            $allowed = array_keys($this->nextStagesFor($row));

            if (! in_array($toStatus, $allowed, true)) {
                throw new InvalidArgumentException(__('The selected stage is not allowed from the current status.'));
            }

            $reason = $toStatus === OnboardingStatusMachine::CANCELLED
                ? __('Cancelled by admin.')
                : null;

            app(OnboardingAdminService::class)->transition(
                $row,
                $toStatus,
                'admin',
                auth()->id(),
                $reason
            );
        }, __('Stage updated.'));

        unset($this->stageSelections[$id]);
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

        $nextStagesById = [];
        foreach ($requests as $request) {
            $nextStagesById[$request->id] = $this->nextStagesFor($request);
        }

        return view('apipartnerfizahub::livewire.onboarding-index', [
            'requests' => $requests,
            'counts' => $counts,
            'totalCount' => array_sum($counts),
            'statusOptions' => OnboardingStatusMachine::labels(),
            'nextStagesById' => $nextStagesById,
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('FizaHUB onboarding'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    protected function nextStagesFor(PartnerOnboardingRequest $request): array
    {
        $from = (string) $request->status;
        $targets = OnboardingStatusMachine::transitions()[$from]
            ?? OnboardingStatusMachine::transitions()[OnboardingStatusMachine::publicStatus($from)]
            ?? [];
        $labels = OnboardingStatusMachine::labels();
        $options = [];

        foreach ($targets as $code) {
            $public = OnboardingStatusMachine::publicStatus($code);
            $options[$public] = $labels[$public] ?? $public;
        }

        return $options;
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
