<?php

namespace Modules\AppBookingPages\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'lb_bookings';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
        ];
    }
}
