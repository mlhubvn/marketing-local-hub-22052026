<?php

namespace Modules\AppAdvancedCustomerCrm\Models;

use Illuminate\Database\Eloquent\Model;

class CrmAutomationLog extends Model
{
    protected $table = 'lb_crm_automation_logs';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
    ];
}
