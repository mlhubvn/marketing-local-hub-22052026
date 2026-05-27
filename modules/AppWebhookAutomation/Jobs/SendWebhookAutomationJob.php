<?php

namespace Modules\AppWebhookAutomation\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\AppWebhookAutomation\Support\WebhookAutomationService;

class SendWebhookAutomationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public int $logId) {}

    public function handle(WebhookAutomationService $service): void
    {
        $service->sendLog($this->logId);
    }
}
