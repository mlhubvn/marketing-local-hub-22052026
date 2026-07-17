<?php

namespace Modules\APIPartnerFizaHUB\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\APIPartnerFizaHUB\Services\WebhookOutboxService;

class DeliverPartnerWebhookJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;

    public function __construct(public int $outboxId) {}

    public function handle(WebhookOutboxService $webhooks): void
    {
        $webhooks->deliver($this->outboxId);
    }
}
