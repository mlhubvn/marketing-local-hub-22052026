<?php

namespace Modules\AppAdvancedCustomerCrm\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerMergeLog extends Model
{
    protected $table = 'lb_customer_merge_logs';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];
}
