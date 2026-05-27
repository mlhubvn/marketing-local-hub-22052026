<?php

namespace Modules\AppWebhookAutomation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookAutomationLog extends Model
{
    protected $table = 'lb_webhook_automation_logs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'request_headers' => 'array',
            'request_payload' => 'array',
            'queued_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function automation(): BelongsTo
    {
        return $this->belongsTo(WebhookAutomation::class, 'automation_id');
    }
}
