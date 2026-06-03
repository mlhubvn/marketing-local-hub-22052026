<?php

namespace Modules\AppWhatsAppNotification\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppWhatsAppNotification\Models\WhatsAppNotification;
use Modules\AppWhatsAppNotification\Models\WhatsAppTemplate;
use Modules\AppWhatsAppNotification\Support\WhatsAppNotificationCatalog;
use Modules\AppWhatsAppNotification\Support\WhatsAppNotificationService;

#[Title('WhatsApp Notifications')]
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
    public string $whatsapp_template_id = '';
    public string $send_to = 'customer';
    public string $custom_phone = '';
    public bool $require_customer_phone = true;
    public string $phone_number_id = '';
    public string $access_token = '';
    public string $template_name = '';
    public string $template_language = 'en_US';
    public string $condition_field = '';
    public string $condition_operator = '=';
    public string $condition_value = '';
    public ?string $statusMessage = null;

    public function mount(WhatsAppNotificationService $service): void
    {
        $service->ensureSystemTemplates();
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->canUsePlanFeature('whatsapp_notification'), 403);

        $limit = (int) (auth()->user()?->planLimit('max_whatsapp_notifications', -1) ?? -1);
        if ($limit >= 0 && WhatsAppNotification::query()->where('user_id', auth()->id())->count() >= $limit) {
            $this->addError('name', __('Your current plan allows up to :limit WhatsApp notification rules.', ['limit' => $limit]));
            return;
        }

        $payload = $this->validate([
            'business_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'trigger_event' => ['required', 'string', 'in:'.implode(',', array_keys(WhatsAppNotificationCatalog::triggers()))],
            'status' => ['required', 'string', 'in:draft,active,inactive'],
            'delay_type' => ['required', 'string', 'in:immediate,after,before_booking'],
            'delay_value' => ['integer', 'min:0', 'max:365'],
            'delay_unit' => ['required', 'string', 'in:minutes,hours,days'],
            'whatsapp_template_id' => ['required', 'integer'],
            'send_to' => ['required', 'string', 'in:customer,business,custom'],
            'custom_phone' => ['nullable', 'string', 'max:40'],
            'require_customer_phone' => ['boolean'],
            'phone_number_id' => ['required', 'string', 'max:100'],
            'access_token' => ['required', 'string', 'max:2000'],
            'template_name' => ['nullable', 'string', 'max:255'],
            'template_language' => ['required', 'string', 'max:12'],
            'condition_field' => ['nullable', 'string', 'max:80'],
            'condition_operator' => ['required', 'string', 'in:=,!=,>,<,>=,<=,contains,exists,not_exists'],
            'condition_value' => ['nullable', 'string', 'max:255'],
        ]);

        if (filled($payload['business_id'])) {
            LocalBusiness::query()->where('user_id', auth()->id())->findOrFail((int) $payload['business_id']);
        }

        WhatsAppTemplate::query()
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', auth()->id()))
            ->findOrFail((int) $payload['whatsapp_template_id']);

        $conditions = ['rules' => []];
        if ($this->require_customer_phone && $payload['send_to'] === 'customer') {
            $conditions['rules'][] = ['field' => 'customer.phone', 'operator' => 'exists', 'value' => true];
        }
        if (filled($payload['condition_field'])) {
            $conditions['rules'][] = [
                'field' => $payload['condition_field'],
                'operator' => $payload['condition_operator'],
                'value' => in_array($payload['condition_operator'], ['exists', 'not_exists'], true) ? true : $payload['condition_value'],
            ];
        }

        WhatsAppNotification::query()->create([
            'user_id' => auth()->id(),
            'business_id' => filled($payload['business_id']) ? (int) $payload['business_id'] : null,
            'name' => $payload['name'],
            'trigger_event' => $payload['trigger_event'],
            'status' => $payload['status'],
            'delay_type' => $payload['delay_type'],
            'delay_value' => in_array($payload['delay_type'], ['after', 'before_booking'], true) ? (int) $payload['delay_value'] : 0,
            'delay_unit' => $payload['delay_unit'],
            'condition_json' => $conditions,
            'action_json' => ['type' => 'send_whatsapp'],
            'whatsapp_template_id' => (int) $payload['whatsapp_template_id'],
            'send_to' => $payload['send_to'],
            'custom_phone' => $payload['send_to'] === 'custom' ? $payload['custom_phone'] : null,
            'phone_number_id' => $payload['phone_number_id'],
            'access_token' => $payload['access_token'],
            'template_name' => $payload['template_name'] ?: null,
            'template_language' => $payload['template_language'],
            'created_by' => auth()->id(),
        ]);

        $this->reset(['name', 'custom_phone', 'template_name']);
        $this->status = 'draft';
        $this->delay_type = 'immediate';
        $this->delay_value = 0;
        $this->send_to = 'customer';
        $this->condition_field = '';
        $this->condition_operator = '=';
        $this->condition_value = '';
        $this->statusMessage = __('WhatsApp notification saved.');
        $this->dispatch('whatsapp-notification-saved');
    }

    public function toggle(int $id): void
    {
        $automation = WhatsAppNotification::query()->where('user_id', auth()->id())->findOrFail($id);
        $automation->update(['status' => $automation->status === 'active' ? 'inactive' : 'active']);
        $this->statusMessage = __('Notification status updated.');
    }

    public function delete(int $id): void
    {
        WhatsAppNotification::query()->where('user_id', auth()->id())->whereKey($id)->delete();
        $this->statusMessage = __('Notification deleted.');
    }

    public function render(): View
    {
        $businesses = LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get();
        if ($this->business_id === '' && $businesses->isNotEmpty()) {
            $this->business_id = (string) $businesses->first()->id;
        }

        $templates = WhatsAppTemplate::query()
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', auth()->id()))
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        if ($this->whatsapp_template_id === '' && $templates->isNotEmpty()) {
            $this->whatsapp_template_id = (string) $templates->first()->id;
        }

        return view('appwhatsappnotification::automations', [
            'businesses' => $businesses,
            'templates' => $templates,
            'triggers' => WhatsAppNotificationCatalog::triggers(),
            'conditionFields' => $this->conditionFields(),
            'conditionValues' => $this->conditionValues(),
            'automations' => WhatsAppNotification::query()
                ->where('user_id', auth()->id())
                ->with('business', 'template')
                ->latest()
                ->paginate(10),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('WhatsApp Notifications')]);
    }

    public function updatedConditionField(): void
    {
        $values = $this->conditionValues()[$this->condition_field] ?? [];
        $this->condition_value = $values !== [] ? (string) array_key_first($values) : '';

        if ($this->condition_field === 'customer.phone') {
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
            'customer.phone' => __('Customer phone'),
        ];
    }

    protected function conditionValues(): array
    {
        return [
            'booking.status' => ['pending' => __('Pending'), 'confirmed' => __('Confirmed'), 'cancelled' => __('Cancelled'), 'completed' => __('Completed')],
            'coupon.status' => ['claimed' => __('Claimed'), 'used' => __('Used'), 'expired' => __('Expired')],
            'lead.status' => ['new' => __('New'), 'contacted' => __('Contacted'), 'converted' => __('Converted'), 'lost' => __('Lost')],
            'review.rating' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'],
        ];
    }
}
