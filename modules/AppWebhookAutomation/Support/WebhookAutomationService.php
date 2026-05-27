<?php

namespace Modules\AppWebhookAutomation\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Modules\AppWebhookAutomation\Jobs\SendWebhookAutomationJob;
use Modules\AppWebhookAutomation\Models\WebhookAutomation;
use Modules\AppWebhookAutomation\Models\WebhookAutomationLog;

class WebhookAutomationService
{
    public function handle(string $trigger, Model $related): void
    {
        $context = $this->context($related);
        $userId = (int) ($context['user_id'] ?? 0);

        if ($userId < 1) {
            return;
        }

        WebhookAutomation::query()
            ->with('business')
            ->where('user_id', $userId)
            ->where('trigger_event', $trigger)
            ->where('status', 'active')
            ->when($context['business_id'] ?? null, fn ($query, $businessId) => $query->where(fn ($inner) => $inner->whereNull('business_id')->orWhere('business_id', $businessId)))
            ->get()
            ->each(function (WebhookAutomation $automation) use ($related, $context, $trigger): void {
                if (! $this->conditionsPass((array) $automation->condition_json, $context)) {
                    $this->skippedLog($automation, $related, $context, $trigger, __('Automation conditions did not match.'));
                    return;
                }

                $this->queueOrSend($automation, $related, $context, $trigger);
            });
    }

    public function queueOrSend(WebhookAutomation $automation, Model $related, array $context, string $trigger): ?WebhookAutomationLog
    {
        if ($this->monthlyLimitReached($automation)) {
            $this->skippedLog($automation, $related, $context, $trigger, __('Monthly webhook limit reached.'));
            return null;
        }

        $headers = $this->headers($automation);
        $payload = $this->payload($trigger, $related, $context);

        $log = WebhookAutomationLog::query()->create([
            'user_id' => (int) $automation->user_id,
            'automation_id' => $automation->id,
            'business_id' => $context['business_id'] ?? null,
            'customer_id' => $context['customer_id'] ?? null,
            'trigger_event' => $trigger,
            'related_type' => $related::class,
            'related_id' => $related->getKey(),
            'action_type' => 'webhook',
            'webhook_url' => $automation->webhook_url,
            'method' => strtoupper((string) $automation->method ?: 'POST'),
            'request_headers' => $headers,
            'request_payload' => $payload,
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        $delayAt = $this->delayAt($automation);

        if ($delayAt && $delayAt->isFuture()) {
            SendWebhookAutomationJob::dispatch($log->id)->delay($delayAt);
        } else {
            SendWebhookAutomationJob::dispatch($log->id);
        }

        return $log;
    }

    public function skippedLog(WebhookAutomation $automation, Model $related, array $context, string $trigger, string $reason): WebhookAutomationLog
    {
        return WebhookAutomationLog::query()->create([
            'user_id' => (int) $automation->user_id,
            'automation_id' => $automation->id,
            'business_id' => $context['business_id'] ?? null,
            'customer_id' => $context['customer_id'] ?? null,
            'trigger_event' => $trigger,
            'related_type' => $related::class,
            'related_id' => $related->getKey(),
            'action_type' => 'webhook',
            'webhook_url' => $automation->webhook_url,
            'method' => strtoupper((string) $automation->method ?: 'POST'),
            'request_headers' => $this->headers($automation),
            'request_payload' => null,
            'status' => 'skipped',
            'error_message' => $reason,
            'queued_at' => now(),
        ]);
    }

    public function sendLog(int $logId): void
    {
        $log = WebhookAutomationLog::query()->find($logId);

        if (! $log || ! in_array($log->status, ['queued', 'failed'], true)) {
            return;
        }

        try {
            $method = strtolower((string) $log->method ?: 'post');
            $request = Http::timeout(20)->acceptJson()->withHeaders((array) $log->request_headers);
            $response = match ($method) {
                'put' => $request->put((string) $log->webhook_url, (array) $log->request_payload),
                'patch' => $request->patch((string) $log->webhook_url, (array) $log->request_payload),
                default => $request->post((string) $log->webhook_url, (array) $log->request_payload),
            };

            $log->forceFill([
                'status' => $response->successful() ? 'success' : 'failed',
                'response_status' => $response->status(),
                'response_body' => mb_substr($response->body(), 0, 20000),
                'sent_at' => now(),
                'error_message' => $response->successful() ? null : __('Webhook returned an error response.'),
            ])->save();
        } catch (\Throwable $exception) {
            $log->forceFill(['status' => 'failed', 'error_message' => $exception->getMessage(), 'sent_at' => now()])->save();
        }
    }

    public function sendTest(string $url, string $method = 'POST', array $headers = [], ?string $secret = null): array
    {
        $automation = new WebhookAutomation([
            'webhook_url' => $url,
            'method' => strtoupper($method),
            'headers_json' => $headers,
            'secret_token' => $secret,
        ]);

        $payload = $this->samplePayload();
        $request = Http::timeout(20)->acceptJson()->withHeaders($this->headers($automation));
        $response = strtolower($method) === 'put'
            ? $request->put($url, $payload)
            : (strtolower($method) === 'patch' ? $request->patch($url, $payload) : $request->post($url, $payload));

        return ['status' => $response->status(), 'body' => mb_substr($response->body(), 0, 2000), 'successful' => $response->successful()];
    }

    public function payload(string $trigger, Model $related, array $context): array
    {
        return [
            'event' => $trigger,
            'business' => [
                'id' => $context['business_id'] ?? null,
                'name' => $context['business_name'] ?? '',
                'phone' => $context['business_phone'] ?? '',
                'email' => $context['business_email'] ?? '',
                'website' => $context['business_website'] ?? '',
                'address' => $context['business_address'] ?? '',
            ],
            'customer' => [
                'id' => $context['customer_id'] ?? null,
                'name' => $context['customer_name'] ?? '',
                'email' => $context['customer_email'] ?? '',
                'phone' => $context['customer_phone'] ?? '',
            ],
            'campaign' => [
                'id' => $context['campaign_id'] ?? null,
                'name' => $context['campaign_name'] ?? '',
                'type' => $context['campaign_type'] ?? '',
                'public_url' => $context['public_page_url'] ?? '',
            ],
            'lead' => [
                'status' => $context['lead_status'] ?? '',
                'message' => $context['feedback_message'] ?? '',
            ],
            'booking' => [
                'service' => $context['booking_service'] ?? '',
                'date' => $context['booking_date'] ?? '',
                'time' => $context['booking_time'] ?? '',
                'status' => $context['booking_status'] ?? '',
            ],
            'coupon' => [
                'code' => $context['coupon_code'] ?? '',
                'status' => $context['coupon.status'] ?? '',
                'expiry' => $context['coupon_expiry'] ?? '',
            ],
            'feedback' => [
                'rating' => $context['review_rating'] ?? '',
                'message' => $context['feedback_message'] ?? '',
            ],
            'meta' => [
                'related_type' => $related::class,
                'related_id' => $related->getKey(),
                'created_at' => now()->toIso8601String(),
            ],
        ];
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
            'campaign_id' => $campaign?->id,
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
            'customer.phone' => (string) ($related->customer_phone ?? $related->phone ?? ''),
            'review.rating' => (int) ($related->rating ?? 0),
        ];
    }

    protected function headers(WebhookAutomation $automation): array
    {
        $headers = array_filter((array) $automation->headers_json, fn ($value) => filled($value));

        if (filled($automation->secret_token)) {
            $headers['Authorization'] = 'Bearer '.$automation->secret_token;
        }

        return $headers;
    }

    protected function delayAt(WebhookAutomation $automation): ?\Illuminate\Support\Carbon
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

    protected function monthlyLimitReached(WebhookAutomation $automation): bool
    {
        $userClass = 'Modules\\AdminUser\\Models\\User';

        if (! class_exists($userClass)) {
            return false;
        }

        $user = $userClass::query()->find($automation->user_id);
        $limit = (int) ($user?->planLimit('webhooks_per_month', -1) ?? -1);

        if ($limit < 0) {
            return false;
        }

        return WebhookAutomationLog::query()
            ->where('user_id', $automation->user_id)
            ->whereIn('status', ['queued', 'success'])
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

    protected function samplePayload(): array
    {
        return [
            'event' => 'lead.submitted',
            'business' => ['id' => 1, 'name' => 'Bloom Spa Studio'],
            'customer' => ['name' => 'Sarah', 'email' => 'sarah@example.com', 'phone' => '+1 555 0101'],
            'campaign' => ['id' => 12, 'name' => 'Free Consultation', 'type' => 'lead'],
            'lead' => ['status' => 'new', 'message' => 'I want a consultation'],
            'meta' => ['test' => true, 'created_at' => now()->toIso8601String()],
        ];
    }
}
