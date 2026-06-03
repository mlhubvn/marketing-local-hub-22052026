<?php

namespace Modules\AppWebhookAutomation\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppWebhookAutomation\Models\WebhookAutomation;
use Modules\AppWebhookAutomation\Support\WebhookAutomationCatalog;
use Modules\AppWebhookAutomation\Support\WebhookAutomationService;

#[Title('Webhook Automations')]
class AutomationsIndex extends Component
{
    use WithPagination;

    public string $business_id = '';
    public string $name = '';
    public string $trigger_event = 'lead.submitted';
    public string $status = 'draft';
    public string $delay_type = 'immediate';
    public int $delay_value = 0;
    public string $delay_unit = 'minutes';
    public string $webhook_url = '';
    public string $method = 'POST';
    public string $headers = '';
    public string $secret_token = '';
    public bool $retry_on_failure = true;
    public string $condition_field = '';
    public string $condition_operator = '=';
    public string $condition_value = '';
    public ?string $statusMessage = null;
    public ?string $testResult = null;

    public function save(): void
    {
        abort_unless(auth()->user()?->canUsePlanFeature('webhook_automation'), 403);

        $limit = (int) (auth()->user()?->planLimit('max_webhook_automations', -1) ?? -1);
        if ($limit >= 0 && WebhookAutomation::query()->where('user_id', auth()->id())->count() >= $limit) {
            $this->addError('name', __('Your current plan allows up to :limit webhook automation rules.', ['limit' => $limit]));
            return;
        }

        $payload = $this->validatedPayload();
        $conditions = ['rules' => []];
        if (filled($payload['condition_field'])) {
            $conditions['rules'][] = [
                'field' => $payload['condition_field'],
                'operator' => $payload['condition_operator'],
                'value' => in_array($payload['condition_operator'], ['exists', 'not_exists'], true) ? true : $payload['condition_value'],
            ];
        }

        WebhookAutomation::query()->create([
            'user_id' => auth()->id(),
            'business_id' => filled($payload['business_id']) ? (int) $payload['business_id'] : null,
            'name' => $payload['name'],
            'trigger_event' => $payload['trigger_event'],
            'status' => $payload['status'],
            'delay_type' => $payload['delay_type'],
            'delay_value' => $payload['delay_type'] === 'after' ? (int) $payload['delay_value'] : 0,
            'delay_unit' => $payload['delay_unit'],
            'condition_json' => $conditions,
            'webhook_url' => $payload['webhook_url'],
            'method' => $payload['method'],
            'headers_json' => $this->parseHeaders($payload['headers']),
            'secret_token' => $payload['secret_token'] ?: null,
            'retry_on_failure' => (bool) $payload['retry_on_failure'],
            'created_by' => auth()->id(),
        ]);

        $this->reset(['name', 'webhook_url', 'headers', 'secret_token', 'condition_field', 'condition_value']);
        $this->status = 'draft';
        $this->delay_type = 'immediate';
        $this->delay_value = 0;
        $this->method = 'POST';
        $this->retry_on_failure = true;
        $this->statusMessage = __('Webhook automation saved.');
        $this->dispatch('webhook-automation-saved');
    }

    public function test(WebhookAutomationService $service): void
    {
        $payload = $this->validate([
            'webhook_url' => ['required', 'url', 'max:2048'],
            'method' => ['required', 'string', 'in:POST,PUT,PATCH'],
            'headers' => ['nullable', 'string', 'max:5000'],
            'secret_token' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $result = $service->sendTest($payload['webhook_url'], $payload['method'], $this->parseHeaders($payload['headers'] ?? ''), $payload['secret_token'] ?: null);
            $this->testResult = __('Test webhook returned HTTP :status.', ['status' => $result['status']]);
        } catch (\Throwable $exception) {
            $this->testResult = __('Test failed: :message', ['message' => $exception->getMessage()]);
        }
    }

    public function toggle(int $id): void
    {
        $automation = WebhookAutomation::query()->where('user_id', auth()->id())->findOrFail($id);
        $automation->update(['status' => $automation->status === 'active' ? 'inactive' : 'active']);
        $this->statusMessage = __('Automation status updated.');
    }

    public function delete(int $id): void
    {
        WebhookAutomation::query()->where('user_id', auth()->id())->whereKey($id)->delete();
        $this->statusMessage = __('Automation deleted.');
    }

    public function render(): View
    {
        return view('appwebhookautomation::automations', [
            'businesses' => LocalBusiness::query()->where('user_id', auth()->id())->orderBy('name')->get(),
            'triggers' => WebhookAutomationCatalog::triggers(),
            'conditionFields' => WebhookAutomationCatalog::conditionFields(),
            'conditionValues' => WebhookAutomationCatalog::conditionValues(),
            'automations' => WebhookAutomation::query()
                ->where('user_id', auth()->id())
                ->with('business')
                ->latest()
                ->paginate(10),
        ])->layout(theme_view('layouts.app', 'app'), ['title' => __('Webhook Automations')]);
    }

    protected function validatedPayload(): array
    {
        return $this->validate([
            'business_id' => ['nullable', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'trigger_event' => ['required', 'string', 'in:'.implode(',', array_keys(WebhookAutomationCatalog::triggers()))],
            'status' => ['required', 'string', 'in:draft,active,inactive'],
            'delay_type' => ['required', 'string', 'in:immediate,after'],
            'delay_value' => ['integer', 'min:0', 'max:365'],
            'delay_unit' => ['required', 'string', 'in:minutes,hours,days'],
            'webhook_url' => ['required', 'url', 'max:2048'],
            'method' => ['required', 'string', 'in:POST,PUT,PATCH'],
            'headers' => ['nullable', 'string', 'max:5000'],
            'secret_token' => ['nullable', 'string', 'max:255'],
            'retry_on_failure' => ['boolean'],
            'condition_field' => ['nullable', 'string', 'max:80'],
            'condition_operator' => ['required', 'string', 'in:=,!=,>,<,>=,<=,contains,exists,not_exists'],
            'condition_value' => ['nullable', 'string', 'max:255'],
        ]);
    }

    protected function parseHeaders(string $headers): array
    {
        if (trim($headers) === '') {
            return [];
        }

        $decoded = json_decode($headers, true);

        return is_array($decoded) ? $decoded : [];
    }
}
