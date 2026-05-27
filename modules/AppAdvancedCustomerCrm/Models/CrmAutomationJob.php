<?php

namespace Modules\AppAdvancedCustomerCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AppCustomers\Models\Customer;

class CrmAutomationJob extends Model
{
    protected $table = 'lb_crm_automation_jobs';

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'run_at' => 'datetime',
        'processed_at' => 'datetime',
    ];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(CrmAutomation::class, 'automation_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
