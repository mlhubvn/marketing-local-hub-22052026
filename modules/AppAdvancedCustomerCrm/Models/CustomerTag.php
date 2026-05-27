<?php

namespace Modules\AppAdvancedCustomerCrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\AppCustomers\Models\Customer;

class CustomerTag extends Model
{
    protected $table = 'lb_customer_tags';

    protected $guarded = [];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'lb_customer_tag_maps', 'tag_id', 'customer_id')
            ->withPivot(['team_id', 'created_by', 'created_at']);
    }
}
