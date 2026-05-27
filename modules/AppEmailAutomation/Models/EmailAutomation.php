<?php

namespace Modules\AppEmailAutomation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class EmailAutomation extends Model
{
    protected $table = 'lb_email_automations';

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
        return $this->belongsTo(EmailTemplate::class, 'email_template_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(EmailAutomationLog::class, 'automation_id');
    }
}
