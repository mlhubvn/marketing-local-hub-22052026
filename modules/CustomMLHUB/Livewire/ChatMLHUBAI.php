<?php

namespace Modules\CustomMLHUB\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Modules\CustomMLHUB\Livewire\Concerns\InteractsWithMLHUBAIAssistant;

#[Title('MLHUB AI')]
class ChatMLHUBAI extends Component
{
    use InteractsWithMLHUBAIAssistant;

    public function mount(): void
    {
        $this->mountAssistant();
    }

    public function render(): View
    {
        return view('custommlhub::chat', [
            'compact' => false,
        ])->layout(theme_view('layouts.app', 'app'), [
            'title' => __('MLHUB AI'),
        ]);
    }
}
