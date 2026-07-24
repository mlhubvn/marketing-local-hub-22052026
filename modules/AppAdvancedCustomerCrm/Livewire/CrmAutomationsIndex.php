<?php

namespace Modules\AppAdvancedCustomerCrm\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppAdvancedCustomerCrm\Models\CrmAutomation;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

#[Title('CRM Automations')]
class CrmAutomationsIndex extends Component
{
    use WithPagination;

    public string $name = 'Low-score follow-up';

    public string $business_id = '';

    public string $trigger_event = 'low_score_feedback_submitted';

    public string $condition_field = '';

    public string $condition_operator = '=';

    public string $condition_value = '';

    public string $action_type = 'create_task';

    public string $action_value = 'feedback.low_score';

    public string $action_title = 'Follow up with customer';

    public string $action_priority = 'high';

    public string $delay_type = 'immediate';

    public string $delay_value = '';

    public string $delay_unit = 'days';

    public string $status = 'active';

    public ?int $editingId = null;

    public ?string $statusMessage = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->canUsePlanFeature('advanced_crm'), 403);
    }

    public function save(): void
    {
        $payload = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'business_id' => ['nullable', 'integer'],
            'trigger_event' => ['required', 'string', 'max:120'],
            'condition_field' => ['nullable', 'string', 'max:80'],
            'condition_operator' => ['nullable', 'string', 'max:20'],
            'condition_value' => ['nullable', 'string', 'max:255'],
            'action_type' => ['required', 'string', 'in:add_tag,change_status,create_task,add_note,increase_score,send_email,send_whatsapp,send_webhook'],
            'action_value' => ['nullable', 'string', 'max:255'],
            'action_title' => ['nullable', 'string', 'max:255'],
            'action_priority' => ['required', 'string', 'in:low,medium,high,urgent'],
            'delay_type' => ['required', 'string', 'in:immediate,after'],
            'delay_value' => ['nullable', 'integer', 'min:1', 'max:365'],
            'delay_unit' => ['required', 'string', 'in:minutes,hours,days'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        $conditions = filled($payload['condition_field']) ? ['rules' => [[
            'field' => $payload['condition_field'],
            'operator' => $payload['condition_operator'] ?: '=',
            'value' => $payload['condition_value'],
        ]]] : ['rules' => []];

        $actions = ['actions' => [[
            'type' => $payload['action_type'],
            'value' => $payload['action_value'],
            'title' => $payload['action_title'] ?: __('Follow up with customer'),
            'priority' => $payload['action_priority'],
        ]]];

        $data = [
            'owner_user_id' => auth()->id(),
            'business_id' => filled($payload['business_id']) ? (int) $payload['business_id'] : null,
            'name' => $payload['name'],
            'trigger_event' => $payload['trigger_event'],
            'condition_json' => $conditions,
            'action_json' => $actions,
            'delay_type' => $payload['delay_type'],
            'delay_value' => $payload['delay_type'] === 'after' ? (int) $payload['delay_value'] : null,
            'delay_unit' => $payload['delay_type'] === 'after' ? $payload['delay_unit'] : null,
            'status' => $payload['status'],
            'created_by' => auth()->id(),
        ];

        if ($this->editingId) {
            CrmAutomation::query()->where('owner_user_id', auth()->id())->whereKey($this->editingId)->update($data);
            $this->statusMessage = __('Automation updated.');
        } else {
            $this->ensureAutomationLimit();
            CrmAutomation::query()->create($data);
            $this->statusMessage = __('Automation created.');
        }

        $this->resetForm();
        $this->resetPage();
        $this->dispatch('crm-automation-saved');
    }

    public function edit(int $id): void
    {
        $automation = CrmAutomation::query()->where('owner_user_id', auth()->id())->findOrFail($id);
        $rule = (array) data_get($automation->condition_json, 'rules.0', []);
        $action = (array) data_get($automation->action_json, 'actions.0', []);

        $this->editingId = $automation->id;
        $this->name = $automation->name;
        $this->business_id = (string) ($automation->business_id ?: '');
        $this->trigger_event = $automation->trigger_event;
        $this->condition_field = (string) data_get($rule, 'field', '');
        $this->condition_operator = (string) data_get($rule, 'operator', '=');
        $this->condition_value = (string) data_get($rule, 'value', '');
        $this->action_type = (string) data_get($action, 'type', 'create_task');
        $this->action_value = (string) data_get($action, 'value', '');
        $this->action_title = (string) data_get($action, 'title', 'Follow up with customer');
        $this->action_priority = (string) data_get($action, 'priority', 'high');
        $this->delay_type = (string) ($automation->delay_type ?: 'immediate');
        $this->delay_value = (string) ($automation->delay_value ?: '');
        $this->delay_unit = (string) ($automation->delay_unit ?: 'days');
        $this->status = $automation->status;
    }

    public function delete(int $id): void
    {
        CrmAutomation::query()->where('owner_user_id', auth()->id())->whereKey($id)->delete();
        $this->statusMessage = __('Automation deleted.');
    }

    public function render(): View
    {
        return view('appadvancedcustomercrm::automations-index', [
            'businesses' => LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get(),
            'triggers' => $this->triggers(),
            'externalTriggers' => $this->externalTriggers(),
            'automations' => CrmAutomation::query()->where('owner_user_id', auth()->id())->withCount('logs')->latest()->paginate(10),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('CRM Automations')]);
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = 'Low-score follow-up';
        $this->business_id = '';
        $this->trigger_event = 'low_score_feedback_submitted';
        $this->condition_field = '';
        $this->condition_operator = '=';
        $this->condition_value = '';
        $this->action_type = 'create_task';
        $this->action_value = 'feedback.low_score';
        $this->action_title = 'Follow up with customer';
        $this->action_priority = 'high';
        $this->delay_type = 'immediate';
        $this->delay_value = '';
        $this->delay_unit = 'days';
        $this->status = 'active';
    }

    protected function triggers(): array
    {
        return [
            'customer_created' => __('Customer created'),
            'customer_inactive' => __('Customer inactive'),
            'note_created' => __('Note created'),
            'task_created' => __('Task created'),
            'loyalty_stamp_added' => __('Loyalty stamp added'),
            'reward_unlocked' => __('Reward unlocked'),
            'referral_converted' => __('Referral converted'),
            'booking_submitted' => __('Booking submitted'),
            'booking_confirmed' => __('Booking confirmed'),
            'booking_completed' => __('Booking completed'),
            'booking_cancelled' => __('Booking cancelled'),
            'coupon_claimed' => __('Coupon claimed'),
            'coupon_used' => __('Coupon used'),
            'feedback_submitted' => __('Feedback submitted'),
            'low_score_feedback_submitted' => __('Low-score feedback submitted'),
            'review_rating_submitted' => __('Review rating submitted'),
            'google_review_synced' => __('Google review synced'),
        ];
    }

    protected function externalTriggers(): array
    {
        return [
            'customer.created' => __('Customer created'),
            'booking.submitted' => __('Booking submitted'),
            'booking.confirmed' => __('Booking confirmed'),
            'booking.cancelled' => __('Booking cancelled'),
            'booking.completed' => __('Booking completed'),
            'coupon.claimed' => __('Coupon claimed'),
            'coupon.used' => __('Coupon used'),
            'feedback.submitted' => __('Feedback submitted'),
            'feedback.low_score' => __('Low-score feedback'),
            'review.positive' => __('Positive review'),
            'review.low_score' => __('Low-score review'),
        ];
    }

    protected function ensureAutomationLimit(): void
    {
        $limit = auth()->user()?->planLimit('crm_automations', -1);
        if ((int) $limit === -1) {
            return;
        }
        abort_if(CrmAutomation::query()->where('owner_user_id', auth()->id())->count() >= (int) $limit, 403, __('Your CRM automation limit has been reached.'));
    }
}
