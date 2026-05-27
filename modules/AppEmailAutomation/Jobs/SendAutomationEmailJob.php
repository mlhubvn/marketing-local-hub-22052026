<?php

namespace Modules\AppEmailAutomation\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\AppEmailAutomation\Support\EmailAutomationService;

class SendAutomationEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $logId) {}

    public function handle(EmailAutomationService $service): void
    {
        $service->sendLog($this->logId);
    }
}
