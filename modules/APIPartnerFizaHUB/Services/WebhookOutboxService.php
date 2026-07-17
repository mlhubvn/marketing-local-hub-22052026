<?php

namespace Modules\APIPartnerFizaHUB\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\APIPartnerFizaHUB\Jobs\DeliverPartnerWebhookJob;
use Modules\APIPartnerFizaHUB\Models\PartnerWebhookOutbox;
use Throwable;

class WebhookOutboxService
{
    public function __construct(
        protected PartnerMappingService $mapping
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function queue(
        string $eventType,
        string $endpointPath,
        array $payload,
        ?string $dedupeKey = null
    ): ?PartnerWebhookOutbox {
        if (! Schema::hasTable('partner_webhook_outbox')) {
            return null;
        }

        $baseUrl = rtrim((string) config('modules.apipartnerfizahub.webhook_base_url', ''), '/');

        if ($baseUrl === '') {
            return null;
        }

        $partnerCode = $this->mapping->partnerCode();
        $dedupeKey = $dedupeKey ?: hash('sha256', $eventType.'|'.json_encode($payload));

        $existing = PartnerWebhookOutbox::query()
            ->where('partner_code', $partnerCode)
            ->where('dedupe_key', $dedupeKey)
            ->first();

        if ($existing) {
            return $existing;
        }

        $row = PartnerWebhookOutbox::query()->create([
            'partner_code' => $partnerCode,
            'event_type' => $eventType,
            'dedupe_key' => $dedupeKey,
            'endpoint_path' => $endpointPath,
            'payload' => $payload,
            'status' => 'pending',
            'attempts' => 0,
            'available_at' => now(),
        ]);

        DB::afterCommit(function () use ($row): void {
            DeliverPartnerWebhookJob::dispatch($row->id);
        });

        return $row;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function queueOnboardingStatus(array $payload, string $dedupeSuffix): ?PartnerWebhookOutbox
    {
        return $this->queue(
            'onboarding-status',
            '/mlhub/onboarding-status',
            $payload,
            'onboarding-status|'.$dedupeSuffix
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function queueCampaignMetrics(array $payload, string $dedupeSuffix): ?PartnerWebhookOutbox
    {
        return $this->queue(
            'campaign-metrics',
            '/mlhub/campaign-metrics',
            $payload,
            'campaign-metrics|'.$dedupeSuffix
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function queueSupportEvent(array $payload, string $dedupeSuffix): ?PartnerWebhookOutbox
    {
        return $this->queue(
            'support-events',
            '/mlhub/support-events',
            $payload,
            'support-events|'.$dedupeSuffix
        );
    }

    public function deliver(int $outboxId): void
    {
        $row = PartnerWebhookOutbox::query()->find($outboxId);

        if (! $row || $row->status === 'delivered') {
            return;
        }

        $baseUrl = rtrim((string) config('modules.apipartnerfizahub.webhook_base_url', ''), '/');
        $secret = (string) config('modules.apipartnerfizahub.webhook_secret', '');

        if ($baseUrl === '') {
            $row->forceFill([
                'status' => 'skipped',
                'last_error' => 'Webhook base URL is not configured.',
            ])->save();

            return;
        }

        $body = json_encode($row->payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
        $timestamp = (string) now()->timestamp;
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, $secret !== '' ? $secret : 'unsigned');

        $url = $baseUrl.$row->endpoint_path;

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Partner' => $row->partner_code,
                    'X-MLHUB-Timestamp' => $timestamp,
                    'X-MLHUB-Signature' => 'sha256='.$signature,
                    'X-Event-Type' => $row->event_type,
                    'X-Dedupe-Key' => $row->dedupe_key,
                ])
                ->withBody($body, 'application/json')
                ->post($url);

            $row->attempts = (int) $row->attempts + 1;

            if ($response->successful()) {
                $row->forceFill([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                    'signature_hash' => hash('sha256', $signature),
                    'last_error' => null,
                ])->save();

                return;
            }

            $row->forceFill([
                'status' => 'failed',
                'available_at' => now()->addMinutes(min(60, max(1, (int) $row->attempts * 2))),
                'last_error' => 'HTTP '.$response->status().': '.Str::limit($response->body(), 500),
            ])->save();
        } catch (Throwable $exception) {
            $row->attempts = (int) $row->attempts + 1;
            $row->forceFill([
                'status' => 'failed',
                'available_at' => now()->addMinutes(min(60, max(1, (int) $row->attempts * 2))),
                'last_error' => Str::limit($exception->getMessage(), 500),
            ])->save();

            Log::warning('FizaHUB webhook delivery failed', [
                'outbox_id' => $outboxId,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function retryPending(int $limit = 50): int
    {
        if (! Schema::hasTable('partner_webhook_outbox')) {
            return 0;
        }

        $rows = PartnerWebhookOutbox::query()
            ->whereIn('status', ['pending', 'failed'])
            ->where(function ($query): void {
                $query->whereNull('available_at')->orWhere('available_at', '<=', now());
            })
            ->where('attempts', '<', 8)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($rows as $row) {
            DeliverPartnerWebhookJob::dispatch($row->id);
        }

        return $rows->count();
    }
}
