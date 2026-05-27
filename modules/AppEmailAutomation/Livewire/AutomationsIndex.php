<?php

namespace Modules\AppEmailAutomation\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppEmailAutomation\Models\EmailAutomation;
use Modules\AppEmailAutomation\Models\EmailTemplate;
use Modules\AppEmailAutomation\Support\EmailAutomationCatalog;
use Modules\AppEmailAutomation\Support\EmailAutomationService;

#[Title('Email Automations')]
class AutomationsIndex extends Component
{
    use WithPagination;

    public string $business_id = '';
    public string $name = '';
    public string $trigger_event = 'booking.submitted';
    public string $status = 'draft';
    public string $delay_type = 'immediate';
    public int $delay_value = 0;
    public string $delay_unit = 'minutes';
    public string $email_template_id = '';
    public string $send_to = 'customer';
    public string $custom_email = '';
    public bool $require_customer_email = true;
    public string $condition_field = '';
    public string $condition_operator = '=';
    public string $condition_value = '';
    public ?string $statusMessage = null;

    public function mount(EmailAutomationService $service): void
    {
        $service->ensureSystemTemplates();
    }

    public function save(): void
    {
        abort_unless(! auth()->user()?->plan || (auth()->user()?->canUsePlanFeature('email_automation') ?? false), 403);

        $limit = (int) (auth()->user()?->planLimit('max_email_automations', -1) ?? -1);
        if ($limit >= 0 && EmailAutomation::query()->where('user_id', auth()->id())->count() >= $limit) {
            $this->addError('name', __('Your current plan allows up to :limit email automations.', ['limit' => $limit]));
            return;
        }

        $payload = $this->validate([
            'business_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'trigger_event' => ['required', 'string', 'in:'.implode(',', array_keys(EmailAutomationCatalog::triggers()))],
            'status' => ['required', 'string', 'in:draft,active,inactive'],
            'delay_type' => ['required', 'string', 'in:immediate,after'],
            'delay_value' => ['integer', 'min:0', 'max:365'],
            'delay_unit' => ['required', 'string', 'in:minutes,hours,days'],
            'email_template_id' => ['required', 'integer'],
            'send_to' => ['required', 'string', 'in:customer,business,custom'],
            'custom_email' => ['nullable', 'email', 'max:255'],
            'require_customer_email' => ['boolean'],
            'condition_field' => ['nullable', 'string', 'max:80'],
            'condition_operator' => ['required', 'string', 'in:=,!=,>,<,>=,<=,contains,exists,not_exists'],
            'condition_value' => ['nullable', 'string', 'max:255'],
        ]);

        if (filled($payload['business_id'])) {
            LocalBusiness::query()->where('user_id', auth()->id())->findOrFail((int) $payload['business_id']);
        }

        EmailTemplate::query()
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', auth()->id()))
            ->findOrFail((int) $payload['email_template_id']);

        $conditions = ['rules' => []];
        if ($this->require_customer_email && $payload['send_to'] === 'customer') {
            $conditions['rules'][] = ['field' => 'customer.email', 'operator' => 'exists', 'value' => true];
        }
        if (filled($payload['condition_field'])) {
            $conditions['rules'][] = [
                'field' => $payload['condition_field'],
                'operator' => $payload['condition_operator'],
                'value' => in_array($payload['condition_operator'], ['exists', 'not_exists'], true) ? true : $payload['condition_value'],
            ];
        }

        EmailAutomation::query()->create([
            'user_id' => auth()->id(),
            'business_id' => filled($payload['business_id']) ? (int) $payload['business_id'] : null,
            'name' => $payload['name'],
            'trigger_event' => $payload['trigger_event'],
            'status' => $payload['status'],
            'delay_type' => $payload['delay_type'],
            'delay_value' => $payload['delay_type'] === 'after' ? (int) $payload['delay_value'] : 0,
            'delay_unit' => $payload['delay_unit'],
            'condition_json' => $conditions,
            'action_json' => ['type' => 'send_email'],
            'email_template_id' => (int) $payload['email_template_id'],
            'send_to' => $payload['send_to'],
            'custom_email' => $payload['send_to'] === 'custom' ? $payload['custom_email'] : null,
            'created_by' => auth()->id(),
        ]);

        $this->reset(['name', 'custom_email']);
        $this->status = 'draft';
        $this->delay_type = 'immediate';
        $this->delay_value = 0;
        $this->send_to = 'customer';
        $this->condition_field = '';
        $this->condition_operator = '=';
        $this->condition_value = '';
        $this->statusMessage = __('Email automation saved.');
        $this->dispatch('email-automation-saved');
    }

    public function toggle(int $id): void
    {
        $automation = EmailAutomation::query()->where('user_id', auth()->id())->findOrFail($id);
        $automation->update(['status' => $automation->status === 'active' ? 'inactive' : 'active']);
        $this->statusMessage = __('Automation status updated.');
    }

    public function delete(int $id): void
    {
        EmailAutomation::query()->where('user_id', auth()->id())->whereKey($id)->delete();
        $this->statusMessage = __('Automation deleted.');
    }

    public function render(): View
    {
        $businesses = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get();
        if ($this->business_id === '' && $businesses->isNotEmpty()) {
            $this->business_id = (string) $businesses->first()->id;
        }

        $templates = EmailTemplate::query()
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', auth()->id()))
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        if ($this->email_template_id === '' && $templates->isNotEmpty()) {
            $this->email_template_id = (string) $templates->first()->id;
        }

        return view('appemailautomation::automations', [
            'businesses' => $businesses,
            'templates' => $templates,
            'triggers' => EmailAutomationCatalog::triggers(),
            'conditionFields' => $this->conditionFields(),
            'conditionValues' => $this->conditionValues(),
            'automations' => EmailAutomation::query()
                ->where('user_id', auth()->id())
                ->with('business', 'template')
                ->latest()
                ->paginate(10),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Email Automations')]);
    }

    public function updatedConditionField(): void
    {
        $values = $this->conditionValues()[$this->condition_field] ?? [];
        $this->condition_value = $values !== [] ? (string) array_key_first($values) : '';

        if ($this->condition_field === 'customer.email') {
            $this->condition_operator = 'exists';
        }
    }

    protected function conditionFields(): array
    {
        return [
            '' => __('No extra condition'),
            'booking.status' => __('Booking status'),
            'coupon.status' => __('Coupon status'),
            'lead.status' => __('Lead status'),
            'review.rating' => __('Review rating'),
            'customer.email' => __('Customer email'),
        ];
    }

    protected function conditionValues(): array
    {
        return [
            'booking.status' => [
                'pending' => __('Pending'),
                'confirmed' => __('Confirmed'),
                'cancelled' => __('Cancelled'),
                'completed' => __('Completed'),
            ],
            'coupon.status' => [
                'claimed' => __('Claimed'),
                'used' => __('Used'),
                'expired' => __('Expired'),
            ],
            'lead.status' => [
                'new' => __('New'),
                'contacted' => __('Contacted'),
                'converted' => __('Converted'),
                'lost' => __('Lost'),
            ],
            'review.rating' => [
                '1' => '1',
                '2' => '2',
                '3' => '3',
                '4' => '4',
                '5' => '5',
            ],
        ];
    }
}
