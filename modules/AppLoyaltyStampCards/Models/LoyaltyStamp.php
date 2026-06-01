<?php

namespace Modules\AppLoyaltyStampCards\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AppCustomers\Models\Customer;

class LoyaltyStamp extends Model
{
    protected $table = 'lb_loyalty_stamps';

    protected $guarded = [];

    public function card(): BelongsTo
    {
        return $this->belongsTo(LoyaltyCard::class, 'card_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
