<?php

namespace Modules\AppWhatsAppNotification\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppWhatsAppNotification\Models\WhatsAppTemplate;
use Modules\AppWhatsAppNotification\Support\WhatsAppNotificationCatalog;
use Modules\AppWhatsAppNotification\Support\WhatsAppNotificationService;

#[Title('WhatsApp Templates')]
class TemplatesIndex extends Component
{
    use WithPagination;

    public string $business_id = '';
    public string $name = '';
    public string $type = 'general';
    public string $template_name = '';
    public string $language = 'en_US';
    public string $body = '';
    public string $status = 'active';
    public ?int $editingTemplateId = null;
    public ?int $previewTemplateId = null;
    public ?string $statusMessage = null;

    public function mount(WhatsAppNotificationService $service): void
    {
        $service->ensureSystemTemplates();
    }

    public function save(): void
    {
        $limit = (int) (auth()->user()?->planLimit('max_whatsapp_templates', -1) ?? -1);
        if (! $this->editingTemplateId && $limit >= 0 && WhatsAppTemplate::query()->where('user_id', auth()->id())->where('is_system', false)->count() >= $limit) {
            $this->addError('name', __('Your current plan allows up to :limit WhatsApp templates.', ['limit' => $limit]));
            return;
        }

        $payload = $this->validate([
            'business_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:80'],
            'template_name' => ['nullable', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:12'],
            'body' => ['required', 'string', 'max:4096'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $attributes = [
            'user_id' => auth()->id(),
            'business_id' => filled($payload['business_id']) ? (int) $payload['business_id'] : null,
            'name' => $payload['name'],
            'type' => $payload['type'],
            'template_name' => $payload['template_name'] ?: null,
            'language' => $payload['language'],
            'body' => $payload['body'],
            'status' => $payload['status'],
            'is_system' => false,
        ];

        if ($this->editingTemplateId) {
            WhatsAppTemplate::query()
                ->where('user_id', auth()->id())
                ->where('is_system', false)
                ->whereKey($this->editingTemplateId)
                ->firstOrFail()
                ->update($attributes);
        } else {
            WhatsAppTemplate::query()->create($attributes);
        }

        $this->reset(['name', 'template_name', 'body']);
        $this->editingTemplateId = null;
        $this->statusMessage = __('WhatsApp template saved.');
        $this->dispatch('whatsapp-template-saved');
    }

    public function edit(int $id): void
    {
        $template = WhatsAppTemplate::query()->where('user_id', auth()->id())->where('is_system', false)->findOrFail($id);

        $this->editingTemplateId = $template->id;
        $this->business_id = $template->business_id ? (string) $template->business_id : '';
        $this->name = (string) $template->name;
        $this->type = (string) $template->type;
        $this->template_name = (string) $template->template_name;
        $this->language = (string) $template->language;
        $this->body = (string) $template->body;
        $this->status = (string) $template->status;
        $this->dispatch('whatsapp-template-editing');
    }

    public function preview(int $id): void
    {
        WhatsAppTemplate::query()
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', auth()->id()))
            ->findOrFail($id);

        $this->previewTemplateId = $id;
        $this->dispatch('whatsapp-template-previewing');
    }

    public function duplicate(int $id): void
    {
        $template = WhatsAppTemplate::query()
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', auth()->id()))
            ->findOrFail($id);

        $copy = $template->replicate(['is_system']);
        $copy->user_id = auth()->id();
        $copy->is_system = false;
        $copy->name = $template->name.' copy';
        $copy->save();
        $this->statusMessage = __('Template duplicated.');
    }

    public function delete(int $id): void
    {
        WhatsAppTemplate::query()->where('user_id', auth()->id())->where('is_system', false)->whereKey($id)->delete();
        $this->statusMessage = __('Template deleted.');
    }

    public function render(): View
    {
        return view('appwhatsappnotification::templates', [
            'businesses' => LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get(),
            'variables' => WhatsAppNotificationCatalog::variables(),
            'previewTemplate' => $this->previewTemplateId
                ? WhatsAppTemplate::query()->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', auth()->id()))->find($this->previewTemplateId)
                : null,
            'templates' => WhatsAppTemplate::query()
                ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', auth()->id()))
                ->orderByDesc('is_system')
                ->latest()
                ->paginate(12),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('WhatsApp Templates')]);
    }
}
