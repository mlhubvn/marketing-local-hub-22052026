<?php

namespace Modules\AppWebhookAutomation\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppWebhookAutomation\Models\WebhookAutomation;
use Modules\AppWebhookAutomation\Models\WebhookAutomationLog;
use Modules\AppWebhookAutomation\Support\WebhookAutomationService;

#[Title('Webhook Logs')]
class LogsIndex extends Component
{
    use WithPagination;

    #[Url(except: 'all')]
    public string $status = 'all';

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'all')]
    public string $automation = 'all';

    #[Url(except: 15)]
    public int $perPage = 15;

    public ?string $statusMessage = null;

    public function updated(string $property): void
    {
        if (in_array($property, ['status', 'search', 'automation', 'perPage'], true)) {
            $this->resetPage();
        }
    }

    public function resend(int $id, WebhookAutomationService $service): void
    {
        $log = WebhookAutomationLog::query()->where('user_id', auth()->id())->findOrFail($id);
        $log->forceFill(['status' => 'queued', 'queued_at' => now(), 'error_message' => null])->save();
        $service->sendLog($log->id);
        $this->statusMessage = __('Webhook resent.');
    }

    public function render(): View
    {
        $logsQuery = WebhookAutomationLog::query()
            ->where('user_id', auth()->id())
            ->with('automation')
            ->when($this->status !== 'all', fn ($query) => $query->where('status', $this->status))
            ->when($this->automation !== 'all', fn ($query) => $query->where('automation_id', (int) $this->automation))
            ->when(trim($this->search) !== '', function ($query): void {
                $search = trim($this->search);
                $query->where(fn ($inner) => $inner
                    ->where('webhook_url', 'like', '%'.$search.'%')
                    ->orWhere('trigger_event', 'like', '%'.$search.'%')
                    ->orWhere('error_message', 'like', '%'.$search.'%'));
            });

        return view('appwebhookautomation::logs', [
            'logs' => $logsQuery->latest()->paginate($this->perPage),
            'automations' => WebhookAutomation::query()->where('user_id', auth()->id())->orderBy('name')->get(['id', 'name']),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Webhook Logs')]);
    }
}
