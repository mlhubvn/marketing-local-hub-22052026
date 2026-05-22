<?php

namespace Modules\AppCustomers\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class Customer extends Model
{
    protected $table = 'lb_customers';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'metadata' => 'array',
            'first_seen_at' => 'datetime',
            'last_activity_at' => 'datetime',
            'last_contacted_at' => 'datetime',
            'lifetime_value' => 'decimal:2',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'business_id');
    }
}
