<?php

namespace Modules\CustomMLHUB\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Modules\CustomMLHUB\Livewire\Concerns\InteractsWithMLHUBAIAssistant;

class MLHUBAIDashboardPanel extends Component
{
    use InteractsWithMLHUBAIAssistant;

    public function mount(): void
    {
        if (! auth()->user()?->canUsePlanFeature('mlhub')) {
            return;
        }

        $this->mountAssistant();
    }

    public function render(): View
    {
        if (! auth()->user()?->canUsePlanFeature('mlhub')) {
            return view('custommlhub::components.dashboard-panel-hidden');
        }

        return view('custommlhub::components.dashboard-panel', [
            'compact' => true,
        ]);
    }
}
