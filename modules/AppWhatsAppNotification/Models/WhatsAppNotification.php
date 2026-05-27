<?php

namespace Modules\AppWhatsAppNotification\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class WhatsAppNotification extends Model
{
    protected $table = 'lb_whatsapp_notifications';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'condition_json' => 'array',
            'action_json' => 'array',
            'delay_value' => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'business_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsAppTemplate::class, 'whatsapp_template_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WhatsAppNotificationLog::class, 'automation_id');
    }
}
