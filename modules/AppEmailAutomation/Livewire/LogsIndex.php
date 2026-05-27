<?php

namespace Modules\AppEmailAutomation\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppEmailAutomation\Models\EmailAutomation;
use Modules\AppEmailAutomation\Models\EmailAutomationLog;
use Modules\AppEmailAutomation\Support\EmailAutomationService;

#[Title('Email Logs')]
class LogsIndex extends Component
{
    use WithPagination;

    #[Url(except: 'all')]
    public string $status = 'all';

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $automation = 'all';

    #[Url(except: 'all')]
    public string $dateRange = 'all';

    #[Url(except: 15)]
    public int $perPage = 15;

    public ?string $statusMessage = null;

    public function updated(string $property): void
    {
        if (in_array($property, ['status', 'search', 'automation', 'dateRange', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function resend(int $id, EmailAutomationService $service): void
    {
        $log = EmailAutomationLog::query()->where('user_id', auth()->id())->findOrFail($id);
        $log->forceFill(['status' => 'queued', 'queued_at' => now(), 'error_message' => null])->save();
        $service->sendLog($log->id);
        $this->statusMessage = __('Email resent.');
    }

    public function render(): View
    {
        $logsQuery = EmailAutomationLog::query()
            ->where('user_id', auth()->id())
            ->with('automation', 'template')
            ->when($this->status !== 'all', fn ($query) => $query->where('status', $this->status))
            ->when($this->automation !== 'all', fn ($query) => $query->where('automation_id', (int) $this->automation))
            ->when($this->dateRange !== 'all', function ($query): void {
                $days = (int) $this->dateRange;

                if ($days > 0) {
                    $query->where('created_at', '>=', now()->subDays($days));
                }
            })
            ->when(trim($this->search) !== '', function ($query): void {
                $search = trim($this->search);
                $query->where(fn ($inner) => $inner
                    ->where('recipient_email', 'like', '%'.$search.'%')
                    ->orWhere('recipient_name', 'like', '%'.$search.'%')
                    ->orWhere('subject', 'like', '%'.$search.'%')
                    ->orWhere('trigger_event', 'like', '%'.$search.'%'));
            });

        return view('appemailautomation::logs', [
            'logs' => $logsQuery
                ->latest()
                ->paginate($this->perPage),
            'automations' => EmailAutomation::query()
                ->where('user_id', auth()->id())
                ->orderBy('name')
                ->get(['id', 'name']),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Email Logs')]);
    }
}
