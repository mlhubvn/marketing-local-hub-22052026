<?php

namespace Modules\AppAdvancedCustomerCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AdminUser\Models\User;
use Modules\AppCustomers\Models\Customer;

class CustomerNote extends Model
{
    protected $table = 'lb_customer_notes';

    protected $guarded = [];

    protected $casts = [
        'pinned' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
