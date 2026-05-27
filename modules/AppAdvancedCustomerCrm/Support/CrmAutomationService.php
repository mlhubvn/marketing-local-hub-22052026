<?php

namespace Modules\AppAdvancedCustomerCrm\Support;

use Illuminate\Support\Arr;
use Modules\AppAdvancedCustomerCrm\Models\CrmAutomation;
use Modules\AppAdvancedCustomerCrm\Models\CrmAutomationJob;
use Modules\AppAdvancedCustomerCrm\Models\CrmAutomationLog;
use Modules\AppAdvancedCustomerCrm\Models\CustomerNote;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTag;
use Modules\AppAdvancedCustomerCrm\Models\CustomerTask;
use Modules\AppCustomers\Models\Customer;

class CrmAutomationService
{
    public function handle(string $eventName, Customer $customer, array $payload = []): void
    {
        CrmAutomation::query()
            ->where('status', 'active')
            ->where('trigger_event', $eventName)
            ->where(function ($query) use ($customer): void {
                $query->whereNull('business_id')->orWhere('business_id', $customer->business_id);
            })
            ->get()
            ->each(function (CrmAutomation $automation) use ($eventName, $customer, $payload): void {
                if ($this->shouldDelay($automation)) {
                    $this->queue($automation, $eventName, $customer, $payload);
                    return;
                }

                $this->run($automation, $customer, $payload);
            });
    }

    public function processJob(CrmAutomationJob $job): void
    {
        $job->forceFill(['status' => 'running', 'attempts' => ((int) $job->attempts) + 1])->save();

        if (! $job->automation || ! $job->customer || $job->automation->status !== 'active') {
            $job->forceFill(['status' => 'skipped', 'processed_at' => now(), 'message' => __('Automation or customer is no longer available.')])->save();
            return;
        }

        $this->run($job->automation, $job->customer, (array) $job->payload);
        $job->forceFill(['status' => 'success', 'processed_at' => now(), 'message' => __('Automation job completed.')])->save();
    }

    public function run(CrmAutomation $automation, Customer $customer, array $payload): void
    {
        if (! $this->matches($automation, $customer, $payload)) {
            $this->log($automation, $customer, 'skipped', __('Conditions did not match.'), $payload);
            return;
        }

        try {
            foreach ((array) data_get($automation->action_json, 'actions', []) as $action) {
                $this->runAction((array) $action, $automation, $customer, $payload);
            }

            $this->log($automation, $customer, 'success', __('Automation completed.'), $payload);
        } catch (\Throwable $e) {
            $this->log($automation, $customer, 'failed', $e->getMessage(), $payload);
        }
    }

    protected function matches(CrmAutomation $automation, Customer $customer, array $payload): bool
    {
        $rules = (array) data_get($automation->condition_json, 'rules', []);
        if ($rules === []) {
            return true;
        }

        $results = [];
        foreach ($rules as $rule) {
            $field = (string) data_get($rule, 'field');
            $operator = (string) data_get($rule, 'operator', '=');
            $value = data_get($rule, 'value');
            $actual = data_get($customer, $field, Arr::get($payload, $field));

            $results[] = match ($operator) {
                '>=' => (float) $actual >= (float) $value,
                '<=' => (float) $actual <= (float) $value,
                '>' => (float) $actual > (float) $value,
                '<' => (float) $actual < (float) $value,
                '!=' => (string) $actual !== (string) $value,
                'exists' => filled($actual),
                'not_exists' => blank($actual),
                'contains' => str_contains((string) $actual, (string) $value),
                default => (string) $actual === (string) $value,
            };
        }

        return (string) data_get($automation->condition_json, 'match', 'all') === 'any'
            ? in_array(true, $results, true)
            : ! in_array(false, $results, true);
    }

    protected function runAction(array $action, CrmAutomation $automation, Customer $customer, array $payload): void
    {
        match ((string) data_get($action, 'type')) {
            'add_tag' => $this->addTag($customer, (string) data_get($action, 'value')),
            'change_status' => $customer->forceFill(['status' => (string) data_get($action, 'value', 'active')])->save(),
            'create_task' => CustomerTask::query()->create([
                'team_id' => $customer->team_id ?: $customer->user_id,
                'business_id' => $customer->business_id,
                'customer_id' => $customer->id,
                'assigned_to' => auth()->id() ?: $automation->created_by ?: $customer->user_id,
                'title' => (string) data_get($action, 'title', __('Follow up with customer')),
                'description' => data_get($action, 'description'),
                'type' => 'follow_up',
                'priority' => (string) data_get($action, 'priority', 'medium'),
                'status' => 'open',
                'due_at' => now()->addDays((int) data_get($action, 'due_days', 1)),
                'created_by' => auth()->id() ?: $automation->created_by ?: $customer->user_id,
            ]),
            'add_note' => CustomerNote::query()->create([
                'team_id' => $customer->team_id ?: $customer->user_id,
                'business_id' => $customer->business_id,
                'customer_id' => $customer->id,
                'user_id' => auth()->id() ?: $automation->created_by ?: $customer->user_id,
                'note' => (string) data_get($action, 'value', __('Automation note')),
                'visibility' => 'team',
            ]),
            'increase_score' => app(CustomerScoreService::class)->add($customer, (int) data_get($action, 'value', 1), 'CRM automation'),
            'send_email' => $this->sendEmail($customer, (string) data_get($action, 'value', $this->externalTrigger($automation->trigger_event)), $payload),
            'send_whatsapp' => $this->sendWhatsApp($customer, (string) data_get($action, 'value', $this->externalTrigger($automation->trigger_event)), $payload),
            'send_webhook' => $this->sendWebhook($customer, (string) data_get($action, 'value', $this->externalTrigger($automation->trigger_event)), $payload),
            default => null,
        };
    }

    protected function addTag(Customer $customer, string $tagName): void
    {
        if ($tagName === '') {
            return;
        }

        $tag = CustomerTag::query()->firstOrCreate(
            ['team_id' => $customer->team_id ?: $customer->user_id, 'slug' => str($tagName)->slug()->toString()],
            ['name' => $tagName, 'color' => '#0f766e', 'is_system' => false]
        );

        $customer->crmTags()->syncWithoutDetaching([
            $tag->id => ['team_id' => $tag->team_id, 'created_by' => auth()->id(), 'created_at' => now()],
        ]);
    }

    protected function log(CrmAutomation $automation, Customer $customer, string $status, string $message, array $payload): void
    {
        CrmAutomationLog::query()->create([
            'team_id' => $automation->team_id,
            'automation_id' => $automation->id,
            'customer_id' => $customer->id,
            'status' => $status,
            'message' => $message,
            'payload' => $payload,
        ]);
    }

    protected function shouldDelay(CrmAutomation $automation): bool
    {
        return (string) $automation->delay_type === 'after' && (int) $automation->delay_value > 0;
    }

    protected function queue(CrmAutomation $automation, string $eventName, Customer $customer, array $payload): void
    {
        CrmAutomationJob::query()->create([
            'team_id' => $automation->team_id,
            'automation_id' => $automation->id,
            'customer_id' => $customer->id,
            'event_name' => $eventName,
            'payload' => $payload,
            'status' => 'pending',
            'run_at' => $this->runAt($automation),
        ]);

        $this->log($automation, $customer, 'queued', __('Automation queued for delayed processing.'), $payload);
    }

    protected function runAt(CrmAutomation $automation): \Illuminate\Support\Carbon
    {
        return match ((string) $automation->delay_unit) {
            'hours' => now()->addHours((int) $automation->delay_value),
            'days' => now()->addDays((int) $automation->delay_value),
            default => now()->addMinutes((int) $automation->delay_value),
        };
    }

    protected function sendEmail(Customer $customer, string $trigger, array $payload): void
    {
        if (! class_exists('Modules\\AppEmailAutomation\\Support\\EmailAutomationService')) {
            throw new \RuntimeException(__('Email Automation addon is not installed.'));
        }

        $customer->forceFill(['last_contacted_at' => now()])->save();
        $customer->setAttribute('customer_id', $customer->id);
        app('Modules\\AppEmailAutomation\\Support\\EmailAutomationService')->handle($trigger, $customer);
    }

    protected function sendWhatsApp(Customer $customer, string $trigger, array $payload): void
    {
        if (! class_exists('Modules\\AppWhatsAppNotification\\Support\\WhatsAppNotificationService')) {
            throw new \RuntimeException(__('WhatsApp Notification addon is not installed.'));
        }

        $customer->forceFill(['last_contacted_at' => now()])->save();
        $customer->setAttribute('customer_id', $customer->id);
        app('Modules\\AppWhatsAppNotification\\Support\\WhatsAppNotificationService')->handle($trigger, $customer);
    }

    protected function sendWebhook(Customer $customer, string $trigger, array $payload): void
    {
        if (! class_exists('Modules\\AppWebhookAutomation\\Support\\WebhookAutomationService')) {
            throw new \RuntimeException(__('Webhook Automation addon is not installed.'));
        }

        $customer->setAttribute('customer_id', $customer->id);
        app('Modules\\AppWebhookAutomation\\Support\\WebhookAutomationService')->handle($trigger, $customer);
    }

    protected function externalTrigger(string $eventName): string
    {
        return [
            'customer_created' => 'customer.created',
            'booking_submitted' => 'booking.submitted',
            'booking_confirmed' => 'booking.confirmed',
            'booking_completed' => 'booking.completed',
            'booking_cancelled' => 'booking.cancelled',
            'coupon_claimed' => 'coupon.claimed',
            'coupon_used' => 'coupon.used',
            'feedback_submitted' => 'feedback.submitted',
            'low_score_feedback_submitted' => 'feedback.low_score',
            'review_rating_submitted' => 'review.positive',
            'google_review_synced' => 'review.positive',
            'referral_converted' => 'customer.created',
            'loyalty_stamp_added' => 'customer.created',
            'reward_unlocked' => 'coupon.claimed',
        ][$eventName] ?? $eventName;
    }
}
