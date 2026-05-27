<?php

namespace Modules\AppWhatsAppNotification\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Modules\AppWhatsAppNotification\Jobs\SendWhatsAppNotificationJob;
use Modules\AppWhatsAppNotification\Models\WhatsAppNotification;
use Modules\AppWhatsAppNotification\Models\WhatsAppNotificationLog;
use Modules\AppWhatsAppNotification\Models\WhatsAppTemplate;

class WhatsAppNotificationService
{
    public function ensureSystemTemplates(): void
    {
        foreach (WhatsAppNotificationCatalog::templateSeeds() as $seed) {
            WhatsAppTemplate::query()->firstOrCreate(
                ['user_id' => null, 'is_system' => true, 'name' => $seed['name']],
                [
                    'type' => $seed['type'],
                    'template_name' => $seed['template_name'] ?? null,
                    'language' => $seed['language'] ?? 'en_US',
                    'body' => $seed['body'],
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

        WhatsAppNotification::query()
            ->with('template', 'business')
            ->where('user_id', $userId)
            ->where('trigger_event', $trigger)
            ->where('status', 'active')
            ->when($context['business_id'] ?? null, fn ($query, $businessId) => $query->where(fn ($inner) => $inner->whereNull('business_id')->orWhere('business_id', $businessId)))
            ->get()
            ->each(function (WhatsAppNotification $automation) use ($related, $context, $trigger): void {
                if (! $this->conditionsPass((array) $automation->condition_json, $context)) {
                    $this->skippedLog($automation, $related, $context, $trigger, __('Automation conditions did not match.'));
                    return;
                }

                $this->queueOrSend($automation, $related, $context, $trigger);
            });
    }

    public function queueOrSend(WhatsAppNotification $automation, Model $related, array $context, string $trigger): ?WhatsAppNotificationLog
    {
        $template = $automation->template;
        $recipient = $this->recipient($automation, $context);

        if (! $template || ! $recipient['phone']) {
            $this->skippedLog($automation, $related, $context, $trigger, ! $template ? __('WhatsApp template is missing or inactive.') : __('Recipient phone is missing.'));
            return null;
        }

        if ($this->monthlyLimitReached($automation)) {
            $this->skippedLog($automation, $related, $context, $trigger, __('Monthly WhatsApp message limit reached.'));
            return null;
        }

        $variables = $this->variables($context);
        $body = strtr((string) $template->body, $variables);

        $log = WhatsAppNotificationLog::query()->create([
            'user_id' => (int) $automation->user_id,
            'automation_id' => $automation->id,
            'whatsapp_template_id' => $template->id,
            'business_id' => $context['business_id'] ?? null,
            'customer_id' => $context['customer_id'] ?? null,
            'trigger_event' => $trigger,
            'related_type' => $related::class,
            'related_id' => $related->getKey(),
            'recipient_phone' => $this->normalizePhone($recipient['phone']),
            'recipient_name' => $recipient['name'],
            'message_type' => filled($automation->template_name ?: $template->template_name) ? 'template' : 'text',
            'template_name' => $automation->template_name ?: $template->template_name,
            'template_language' => $automation->template_language ?: $template->language,
            'body' => $body,
            'status' => 'queued',
            'queued_at' => now(),
        ]);

        $delayAt = $this->delayAt($automation, $context);

        if ($delayAt && $delayAt->isFuture()) {
            SendWhatsAppNotificationJob::dispatch($log->id)->delay($delayAt);
        } else {
            SendWhatsAppNotificationJob::dispatch($log->id);
        }

        return $log;
    }

    protected function skippedLog(WhatsAppNotification $automation, Model $related, array $context, string $trigger, string $reason): WhatsAppNotificationLog
    {
        $recipient = $this->recipient($automation, $context);

        return WhatsAppNotificationLog::query()->create([
            'user_id' => (int) $automation->user_id,
            'automation_id' => $automation->id,
            'whatsapp_template_id' => $automation->whatsapp_template_id,
            'business_id' => $context['business_id'] ?? null,
            'customer_id' => $context['customer_id'] ?? null,
            'trigger_event' => $trigger,
            'related_type' => $related::class,
            'related_id' => $related->getKey(),
            'recipient_phone' => $recipient['phone'] ? $this->normalizePhone($recipient['phone']) : 'skipped',
            'recipient_name' => $recipient['name'] ?: null,
            'message_type' => 'text',
            'body' => null,
            'status' => 'skipped',
            'error_message' => $reason,
            'queued_at' => now(),
        ]);
    }

    public function sendLog(int $logId): void
    {
        $log = WhatsAppNotificationLog::query()->with('automation')->find($logId);

        if (! $log || ! in_array($log->status, ['queued', 'failed'], true)) {
            return;
        }

        $automation = $log->automation;

        if (! $automation || blank($automation->phone_number_id) || blank($automation->access_token)) {
            $log->forceFill(['status' => 'failed', 'error_message' => __('WhatsApp Cloud API credentials are missing.')])->save();
            return;
        }

        try {
            $payload = $this->cloudApiPayload($log);
            $response = Http::withToken((string) $automation->access_token)
                ->acceptJson()
                ->post('https://graph.facebook.com/v20.0/'.rawurlencode((string) $automation->phone_number_id).'/messages', $payload);

            if (! $response->successful()) {
                throw new \RuntimeException($response->body());
            }

            $log->forceFill([
                'status' => 'sent',
                'provider_message_id' => (string) data_get($response->json(), 'messages.0.id'),
                'sent_at' => now(),
                'error_message' => null,
            ])->save();
        } catch (\Throwable $exception) {
            $log->forceFill(['status' => 'failed', 'error_message' => $exception->getMessage()])->save();
        }
    }

    protected function cloudApiPayload(WhatsAppNotificationLog $log): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $log->recipient_phone,
        ];

        if ($log->message_type === 'template' && filled($log->template_name)) {
            $payload['type'] = 'template';
            $payload['template'] = [
                'name' => $log->template_name,
                'language' => ['code' => $log->template_language ?: 'en_US'],
            ];

            return $payload;
        }

        $payload['type'] = 'text';
        $payload['text'] = [
            'preview_url' => true,
            'body' => (string) $log->body,
        ];

        return $payload;
    }

    protected function delayAt(WhatsAppNotification $automation, array $context): ?\Illuminate\Support\Carbon
    {
        if ((int) $automation->delay_value < 1) {
            return null;
        }

        if ((string) $automation->delay_type === 'before_booking') {
            $date = trim((string) ($context['booking_date'] ?? ''));
            $time = trim((string) ($context['booking_time'] ?? '00:00'));

            if ($date === '') {
                return null;
            }

            $bookingAt = \Illuminate\Support\Carbon::parse($date.' '.$time);

            return match ((string) $automation->delay_unit) {
                'hours' => $bookingAt->subHours((int) $automation->delay_value),
                'days' => $bookingAt->subDays((int) $automation->delay_value),
                default => $bookingAt->subMinutes((int) $automation->delay_value),
            };
        }

        if ((string) $automation->delay_type !== 'after') {
            return null;
        }

        return match ((string) $automation->delay_unit) {
            'hours' => now()->addHours((int) $automation->delay_value),
            'days' => now()->addDays((int) $automation->delay_value),
            default => now()->addMinutes((int) $automation->delay_value),
        };
    }

    protected function recipient(WhatsAppNotification $automation, array $context): array
    {
        return match ((string) $automation->send_to) {
            'business' => ['phone' => (string) ($context['business_phone'] ?? ''), 'name' => (string) ($context['business_name'] ?? '')],
            'custom' => ['phone' => (string) $automation->custom_phone, 'name' => null],
            default => ['phone' => (string) ($context['customer_phone'] ?? ''), 'name' => (string) ($context['customer_name'] ?? '')],
        };
    }

    protected function monthlyLimitReached(WhatsAppNotification $automation): bool
    {
        $userClass = 'Modules\\AdminUser\\Models\\User';

        if (! class_exists($userClass)) {
            return false;
        }

        $user = $userClass::query()->find($automation->user_id);
        $limit = (int) ($user?->planLimit('whatsapp_messages_per_month', -1) ?? -1);

        if ($limit < 0) {
            return false;
        }

        return WhatsAppNotificationLog::query()
            ->where('user_id', $automation->user_id)
            ->whereIn('status', ['queued', 'sent'])
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

    protected function normalizePhone(string $phone): string
    {
        return ltrim(preg_replace('/[^\d+]/', '', $phone) ?: '', '+');
    }

    public function context(Model $related): array
    {
        $loyaltyContext = $this->loyaltyStampContext($related);

        if ($loyaltyContext !== null) {
            return $loyaltyContext;
        }

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
            'customer.phone' => (string) ($related->customer_phone ?? $related->phone ?? ''),
            'review.rating' => (int) ($related->rating ?? 0),
        ];
    }

    protected function loyaltyStampContext(Model $related): ?array
    {
        $loyaltyStampClass = 'Modules\\AppLoyaltyStampCards\\Models\\LoyaltyStamp';
        $loyaltyCustomerClass = 'Modules\\AppLoyaltyStampCards\\Models\\LoyaltyCustomer';

        if (! class_exists($loyaltyStampClass) || ! ($related instanceof $loyaltyStampClass)) {
            return null;
        }

        $related->loadMissing(['card.business', 'customer']);

        $card = $related->card;
        $business = $card?->business;
        $customer = $related->customer;
        $progress = null;

        if (class_exists($loyaltyCustomerClass) && $card && $customer) {
            $progress = $loyaltyCustomerClass::query()
                ->where('card_id', $card->id)
                ->where('customer_id', $customer->id)
                ->first();
        }

        return [
            'user_id' => (int) ($card?->user_id ?? $business?->user_id ?? 0),
            'business_id' => $business?->id,
            'business_name' => (string) ($business?->name ?? ''),
            'business_phone' => (string) ($business?->phone ?? ''),
            'business_email' => (string) ($business?->email ?? ''),
            'business_website' => (string) ($business?->website ?? ''),
            'business_address' => (string) ($business?->address ?? ''),
            'customer_id' => $customer?->id,
            'customer_name' => (string) ($customer?->name ?? ''),
            'customer_email' => (string) ($customer?->email ?? ''),
            'customer_phone' => (string) ($customer?->phone ?? ''),
            'campaign_name' => (string) ($card?->name ?? ''),
            'campaign_type' => 'loyalty_stamp_card',
            'public_page_url' => $card && method_exists($card, 'publicUrl') ? $card->publicUrl() : '',
            'booking_service' => '',
            'booking_date' => '',
            'booking_time' => '',
            'booking_status' => '',
            'coupon_code' => '',
            'coupon.status' => '',
            'coupon_expiry' => '',
            'review_rating' => '',
            'feedback_message' => '',
            'lead_status' => '',
            'lead.status' => '',
            'booking.status' => '',
            'customer.phone' => (string) ($customer?->phone ?? ''),
            'review.rating' => 0,
            'stamp_card_name' => (string) ($card?->name ?? ''),
            'stamp_count' => (string) ($progress?->stamps_count ?? ''),
            'required_stamps' => (string) ($card?->required_stamps ?? ''),
            'reward_title' => (string) ($card?->reward_title ?? ''),
        ];
    }
}
