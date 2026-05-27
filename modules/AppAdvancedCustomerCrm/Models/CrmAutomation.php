<?php

namespace Modules\AppAdvancedCustomerCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmAutomation extends Model
{
    protected $table = 'lb_crm_automations';

    protected $guarded = [];

    protected $casts = [
        'condition_json' => 'array',
        'action_json' => 'array',
    ];

    public function logs(): HasMany
    {
        return $this->hasMany(CrmAutomationLog::class, 'automation_id');
    }
}
