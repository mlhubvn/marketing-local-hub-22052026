<?php

namespace Modules\APIPartnerFizaHUB\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Services\OnboardingAdminService;
use Throwable;

class FizaHubOnboardingCard extends Component
{
    public int $userId;

    public ?string $statusMessage = null;

    public ?string $errorMessage = null;

    public function mount(int $userId): void
    {
        $this->userId = $userId;
    }

    public function markContacted(): void
    {
        $this->runAction(fn (PartnerOnboardingRequest $row) => app(OnboardingAdminService::class)
            ->markContacted($row, auth()->id()), __('Marked as contacted by consultant.'));
    }

    public function markReady(): void
    {
        $this->runAction(fn (PartnerOnboardingRequest $row) => app(OnboardingAdminService::class)
            ->markReady($row, auth()->id()), __('Account marked as ready.'));
    }

    public function markCompleted(): void
    {
        $this->runAction(fn (PartnerOnboardingRequest $row) => app(OnboardingAdminService::class)
            ->markCompleted($row, auth()->id()), __('Onboarding completed.'));
    }

    public function render(): View
    {
        $onboarding = app(OnboardingAdminService::class)->latestForUser($this->userId);

        return view('apipartnerfizahub::livewire.onboarding-card', [
            'onboarding' => $onboarding,
        ]);
    }

    protected function runAction(callable $callback, string $successMessage): void
    {
        $this->statusMessage = null;
        $this->errorMessage = null;

        $row = app(OnboardingAdminService::class)->latestForUser($this->userId);

        if (! $row) {
            $this->errorMessage = __('No FizaHUB onboarding request is linked to this user.');

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
