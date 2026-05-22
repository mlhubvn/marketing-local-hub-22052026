<?php

namespace Modules\AppBookingPages\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class BookingService extends Model
{
    protected $table = 'lb_booking_services';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'available_days' => 'array',
            'time_slots' => 'array',
            'use_business_hours' => 'boolean',
            'slot_interval' => 'integer',
            'buffer_before' => 'integer',
            'buffer_after' => 'integer',
            'service_hours' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'business_id');
    }
}
