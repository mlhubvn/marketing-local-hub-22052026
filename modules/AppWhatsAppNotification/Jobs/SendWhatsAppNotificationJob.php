<?php

namespace Modules\AppWhatsAppNotification\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\AppWhatsAppNotification\Support\WhatsAppNotificationService;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public int $logId) {}

    public function handle(WhatsAppNotificationService $service): void
    {
        $service->sendLog($this->logId);
    }
}
