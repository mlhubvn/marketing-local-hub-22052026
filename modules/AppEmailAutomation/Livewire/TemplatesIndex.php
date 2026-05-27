<?php

namespace Modules\AppEmailAutomation\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppEmailAutomation\Models\EmailTemplate;
use Modules\AppEmailAutomation\Support\EmailAutomationCatalog;
use Modules\AppEmailAutomation\Support\EmailAutomationService;

#[Title('Email Templates')]
class TemplatesIndex extends Component
{
    use WithPagination;

    public string $business_id = '';
    public string $name = '';
    public string $type = 'general';
    public string $subject = '';
    public string $preheader = '';
    public string $body = '';
    public string $language = 'en';
    public string $status = 'active';
    public ?int $editingTemplateId = null;
    public ?int $previewTemplateId = null;
    public ?string $statusMessage = null;

    public function mount(EmailAutomationService $service): void
    {
        $service->ensureSystemTemplates();
    }

    public function save(): void
    {
        $limit = (int) (auth()->user()?->planLimit('max_email_templates', -1) ?? -1);
        if (! $this->editingTemplateId && $limit >= 0 && EmailTemplate::query()->where('user_id', auth()->id())->where('is_system', false)->count() >= $limit) {
            $this->addError('name', __('Your current plan allows up to :limit email templates.', ['limit' => $limit]));
            return;
        }

        $payload = $this->validate([
            'business_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:80'],
            'subject' => ['required', 'string', 'max:255'],
            'preheader' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'language' => ['required', 'string', 'max:12'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $attributes = [
            'user_id' => auth()->id(),
            'business_id' => filled($payload['business_id']) ? (int) $payload['business_id'] : null,
            'name' => $payload['name'],
            'type' => $payload['type'],
            'subject' => $payload['subject'],
            'preheader' => $payload['preheader'] ?: null,
            'body' => $payload['body'],
            'language' => $payload['language'],
            'status' => $payload['status'],
            'is_system' => false,
        ];

        if ($this->editingTemplateId) {
            EmailTemplate::query()
                ->where('user_id', auth()->id())
                ->where('is_system', false)
                ->whereKey($this->editingTemplateId)
                ->firstOrFail()
                ->update($attributes);
        } else {
            EmailTemplate::query()->create($attributes);
        }

        $this->reset(['name', 'subject', 'preheader', 'body']);
        $this->editingTemplateId = null;
        $this->statusMessage = __('Email template saved.');
        $this->dispatch('email-template-saved');
    }

    public function edit(int $id): void
    {
        $template = EmailTemplate::query()->where('user_id', auth()->id())->where('is_system', false)->findOrFail($id);

        $this->editingTemplateId = $template->id;
        $this->business_id = $template->business_id ? (string) $template->business_id : '';
        $this->name = (string) $template->name;
        $this->type = (string) $template->type;
        $this->subject = (string) $template->subject;
        $this->preheader = (string) $template->preheader;
        $this->body = (string) $template->body;
        $this->language = (string) $template->language;
        $this->status = (string) $template->status;
        $this->dispatch('email-template-editing');
    }

    public function preview(int $id): void
    {
        EmailTemplate::query()
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', auth()->id()))
            ->findOrFail($id);

        $this->previewTemplateId = $id;
        $this->dispatch('email-template-previewing');
    }

    public function duplicate(int $id): void
    {
        $template = EmailTemplate::query()
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
        EmailTemplate::query()->where('user_id', auth()->id())->where('is_system', false)->whereKey($id)->delete();
        $this->statusMessage = __('Template deleted.');
    }

    public function render(): View
    {
        return view('appemailautomation::templates', [
            'businesses' => LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get(),
            'variables' => EmailAutomationCatalog::variables(),
            'previewTemplate' => $this->previewTemplateId
                ? EmailTemplate::query()->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', auth()->id()))->find($this->previewTemplateId)
                : null,
            'templates' => EmailTemplate::query()
                ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', auth()->id()))
                ->orderByDesc('is_system')
                ->latest()
                ->paginate(12),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Email Templates')]);
    }
}
