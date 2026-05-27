<?php

namespace Modules\AppEmailAutomation\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Modules\AppEmailAutomation\Jobs\SendAutomationEmailJob;
use Modules\AppEmailAutomation\Models\EmailAutomation;
use Modules\AppEmailAutomation\Models\EmailAutomationLog;
use Modules\AppEmailAutomation\Models\EmailTemplate;

class EmailAutomationService
{
    public function ensureSystemTemplates(): void
    {
        foreach (EmailAutomationCatalog::templateSeeds() as $seed) {
            EmailTemplate::query()->firstOrCreate(
                ['user_id' => null, 'is_system' => true, 'name' => $seed['name']],
                [
                    'type' => $seed['type'],
                    'subject' => $seed['subject'],
                    'preheader' => null,
                    'body' => $seed['body'],
                    'language' => 'en',
                    'status' => 'active',
                ]
            );
        }
    }

    public function handle(string $trigger, Model $related): void
    {
        $context = $this->context($related);
        $userId = (int) ($context['user_id'] ?? 0);

        if ($userId < 1) {
            return;
        }

        EmailAutomation::query()
            ->with('template', 'business')
            ->where('user_id', $userId)
            ->where('trigger_event', $trigger)
            ->where('status', 'active')
            ->when($context['business_id'] ?? null, fn ($query, $businessId) => $query->where(fn ($inner) => $inner->whereNull('business_id')->orWhere('business_id', $businessId)))
            ->get()
            ->each(function (EmailAutomation $automation) use ($related, $context, $trigger): void {
                if (! $this->conditionsPass((array) $automation->condition_json, $context)) {
                    $this->skippedLog($automation, $related, $context, $trigger, __('Automation conditions did not match.'));
                    return;
                }

                $this->queueOrSend($automation, $related, $context, $trigger);
            });
    }

    public function queueOrSend(EmailAutomation $automation, Model $related, array $context, string $trigger): ?EmailAutomationLog
    {
        $template = $automation->template;
        $recipient = $this->recipient($automation, $context);

        if (! $template || ! $recipient['email']) {
            $this->skippedLog($automation, $related, $context, $trigger, ! $template ? __('Email template is missing or inactive.') : __('Recipient email is missing.'));
            return null;
        }

        if ($this->monthlyLimitReached($automation)) {
            $this->skippedLog($automation, $related, $context, $trigger, __('Monthly email limit reached.'));
            return null;
        }

        $variables = $this->variables($context);
        $subject = strtr((string) $template->subject, $variables);
        $body = nl2br(e(strtr((string) $template->body, $variables)));

        $log = EmailAutomationLog::query()->create([
            'user_id' => (int) $automation->user_id,
            'automation_id' => $automation->id,
            'email_template_id' => $template->id,
            'business_id' => $context['business_id'] ?? null,
            'customer_id' => $context['customer_id'] ?? null,
            'trigger_event' => $trigger,
            'related_type' => $related::class,
            'related_id' => $related->getKey(),
            'recipient_email' => $recipient['email'],
            'recipient_name' => $recipient['name'],
            'subject' => $subject,
            'body' => $body,
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        $delayAt = $this->delayAt($automation, $context);

        if ($delayAt && $delayAt->isFuture()) {
            SendAutomationEmailJob::dispatch($log->id)->delay($delayAt);
        } else {
            SendAutomationEmailJob::dispatch($log->id);
        }

        return $log;
    }

    protected function skippedLog(EmailAutomation $automation, Model $related, array $context, string $trigger, string $reason): EmailAutomationLog
    {
        $recipient = $this->recipient($automation, $context);

        return EmailAutomationLog::query()->create([
            'user_id' => (int) $automation->user_id,
            'automation_id' => $automation->id,
            'email_template_id' => $automation->email_template_id,
            'business_id' => $context['business_id'] ?? null,
            'customer_id' => $context['customer_id'] ?? null,
            'trigger_event' => $trigger,
            'related_type' => $related::class,
            'related_id' => $related->getKey(),
            'recipient_email' => $recipient['email'] ?: 'skipped@localboost.invalid',
            'recipient_name' => $recipient['name'] ?: null,
            'subject' => __('Skipped automation email'),
            'body' => null,
            'status' => 'skipped',
            'error_message' => $reason,
            'queued_at' => now(),
        ]);
    }

    public function sendLog(int $logId): void
    {
        $log = EmailAutomationLog::query()->find($logId);

        if (! $log || ! in_array($log->status, ['queued', 'failed'], true)) {
            return;
        }

        try {
            Mail::html((string) $log->body, function ($message) use ($log): void {
                $message->to($log->recipient_email, $log->recipient_name ?: null)->subject($log->subject);
            });

            $log->forceFill(['status' => 'sent', 'sent_at' => now(), 'error_message' => null])->save();
        } catch (\Throwable $exception) {
            $log->forceFill(['status' => 'failed', 'error_message' => $exception->getMessage()])->save();
        }
    }

    protected function delayAt(EmailAutomation $automation, array $context): ?\Illuminate\Support\Carbon
    {
        if ((string) $automation->delay_type !== 'after' || (int) $automation->delay_value < 1) {
            return null;
        }

        return match ((string) $automation->delay_unit) {
            'hours' => now()->addHours((int) $automation->delay_value),
            'days' => now()->addDays((int) $automation->delay_value),
            default => now()->addMinutes((int) $automation->delay_value),
        };
    }

    protected function recipient(EmailAutomation $automation, array $context): array
    {
        return match ((string) $automation->send_to) {
            'business' => ['email' => (string) ($context['business_email'] ?? ''), 'name' => (string) ($context['business_name'] ?? '')],
            'custom' => ['email' => (string) $automation->custom_email, 'name' => null],
            default => ['email' => (string) ($context['customer_email'] ?? ''), 'name' => (string) ($context['customer_name'] ?? '')],
        };
    }

    protected function monthlyLimitReached(EmailAutomation $automation): bool
    {
        $userClass = 'Modules\\AdminUser\\Models\\User';

        if (! class_exists($userClass)) {
            return false;
        }

        $user = $userClass::query()->find($automation->user_id);
        $limit = (int) ($user?->planLimit('emails_per_month', -1) ?? -1);

        if ($limit < 0) {
            return false;
        }

        return EmailAutomationLog::query()
            ->where('user_id', $automation->user_id)
            ->whereIn('status', ['queued', 'sent', 'opened', 'clicked'])
            ->where('created_at', '>=', now()->startOfMonth())
            ->count() >= $limit;
    }

    protected function conditionsPass(array $conditions, array $context): bool
    {
        foreach ((array) ($conditions['rules'] ?? []) as $rule) {
            $field = (string) ($rule['field'] ?? '');
            $operator = (string) ($rule['operator'] ?? '=');
            $expected = $rule['value'] ?? null;
            $actual = Arr::get($context, $field);

            $passes = match ($operator) {
                'exists' => filled($actual),
                'not_exists' => blank($actual),
                '!=' => (string) $actual !== (string) $expected,
                '>' => (float) $actual > (float) $expected,
                '<' => (float) $actual < (float) $expected,
                '>=' => (float) $actual >= (float) $expected,
                '<=' => (float) $actual <= (float) $expected,
                'contains' => str_contains((string) $actual, (string) $expected),
                default => (string) $actual === (string) $expected,
            };

            if (! $passes) {
                return false;
            }
        }

        return true;
    }

    protected function variables(array $context): array
    {
        return collect($context)
            ->mapWithKeys(fn ($value, $key) => ['{'.$key.'}' => is_scalar($value) ? (string) $value : ''])
            ->all();
    }

    public function context(Model $related): array
    {
        $campaign = method_exists($related, 'campaign') ? $related->campaign()->with('business')->first() : null;
        $business = method_exists($related, 'business') ? $related->business()->first() : ($campaign?->business);
        $service = null;

        if (isset($related->service_id) && class_exists('Modules\\AppBookingPages\\Models\\BookingService')) {
            $service = \Modules\AppBookingPages\Models\BookingService::query()->find($related->service_id);
        }

        return [
            'user_id' => (int) ($related->user_id ?? $business?->user_id ?? $campaign?->user_id ?? 0),
            'business_id' => $business?->id,
            'business_name' => (string) ($business?->name ?? ''),
            'business_phone' => (string) ($business?->phone ?? ''),
            'business_email' => (string) ($business?->email ?? ''),
            'business_website' => (string) ($business?->website ?? ''),
            'business_address' => (string) ($business?->address ?? ''),
            'customer_id' => (int) ($related->customer_id ?? 0) ?: null,
            'customer_name' => (string) ($related->customer_name ?? $related->name ?? ''),
            'customer_email' => (string) ($related->customer_email ?? $related->email ?? ''),
            'customer_phone' => (string) ($related->customer_phone ?? $related->phone ?? ''),
            'campaign_name' => (string) ($campaign?->name ?? ''),
            'campaign_type' => (string) ($campaign?->type ?? ''),
            'public_page_url' => $campaign && method_exists($campaign, 'publicUrl') ? $campaign->publicUrl() : '',
            'booking_service' => (string) ($service?->name ?? ''),
            'booking_date' => (string) ($related->booking_date ?? ''),
            'booking_time' => (string) ($related->booking_time ?? ''),
            'booking_status' => (string) ($related->status ?? ''),
            'coupon_code' => (string) ($related->code ?? ''),
            'coupon.status' => (string) ($related->status ?? ''),
            'coupon_expiry' => (string) data_get($campaign?->settings, 'expiry_date', ''),
            'review_rating' => (string) ($related->rating ?? ''),
            'feedback_message' => (string) ($related->message ?? ''),
            'lead_status' => (string) ($related->status ?? 'new'),
            'lead.status' => (string) ($related->status ?? 'new'),
            'booking.status' => (string) ($related->status ?? ''),
            'customer.email' => (string) ($related->customer_email ?? $related->email ?? ''),
            'review.rating' => (int) ($related->rating ?? 0),
        ];
    }
}
