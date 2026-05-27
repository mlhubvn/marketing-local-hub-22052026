<?php

namespace Modules\AppAdvancedCustomerCrm\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSegment extends Model
{
    protected $table = 'lb_customer_segments';

    protected $guarded = [];

    protected $casts = [
        'filters' => 'array',
        'is_dynamic' => 'boolean',
    ];
}
