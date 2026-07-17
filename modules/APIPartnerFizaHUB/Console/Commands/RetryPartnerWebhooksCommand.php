<?php

namespace Modules\APIPartnerFizaHUB\Console\Commands;

use Illuminate\Console\Command;
use Modules\APIPartnerFizaHUB\Services\WebhookOutboxService;

class RetryPartnerWebhooksCommand extends Command
{
    protected $signature = 'fizahub:webhooks-retry {--limit=50 : Maximum outbox rows to dispatch}';

    protected $description = 'Re-dispatch pending or failed FizaHUB partner webhook deliveries.';

    public function handle(WebhookOutboxService $webhooks): int
    {
        $dispatched = $webhooks->retryPending((int) $this->option('limit'));

        $this->info("Dispatched {$dispatched} FizaHUB webhook deliveries.");

        return self::SUCCESS;
    }
}
