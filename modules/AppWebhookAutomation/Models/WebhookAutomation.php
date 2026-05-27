<?php

namespace Modules\AppWebhookAutomation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class WebhookAutomation extends Model
{
    protected $table = 'lb_webhook_automations';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'condition_json' => 'array',
            'headers_json' => 'array',
            'delay_value' => 'integer',
            'retry_on_failure' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'business_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookAutomationLog::class, 'automation_id');
    }
}
