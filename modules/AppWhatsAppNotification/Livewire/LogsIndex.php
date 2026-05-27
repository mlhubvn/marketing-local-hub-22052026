<?php

namespace Modules\AppWhatsAppNotification\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppWhatsAppNotification\Models\WhatsAppNotification;
use Modules\AppWhatsAppNotification\Models\WhatsAppNotificationLog;
use Modules\AppWhatsAppNotification\Support\WhatsAppNotificationService;

#[Title('WhatsApp Logs')]
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

    public function resend(int $id, WhatsAppNotificationService $service): void
    {
        $log = WhatsAppNotificationLog::query()->where('user_id', auth()->id())->findOrFail($id);
        $log->forceFill(['status' => 'queued', 'queued_at' => now(), 'error_message' => null])->save();
        $service->sendLog($log->id);
        $this->statusMessage = __('WhatsApp message resent.');
    }

    public function render(): View
    {
        $logsQuery = WhatsAppNotificationLog::query()
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
                    ->where('recipient_phone', 'like', '%'.$search.'%')
                    ->orWhere('recipient_name', 'like', '%'.$search.'%')
                    ->orWhere('body', 'like', '%'.$search.'%')
                    ->orWhere('trigger_event', 'like', '%'.$search.'%'));
            });

        return view('appwhatsappnotification::logs', [
            'logs' => $logsQuery
                ->latest()
                ->paginate($this->perPage),
            'automations' => WhatsAppNotification::query()
                ->where('user_id', auth()->id())
                ->orderBy('name')
                ->get(['id', 'name']),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('WhatsApp Logs')]);
    }
}
