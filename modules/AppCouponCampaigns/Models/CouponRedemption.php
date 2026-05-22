<?php

namespace Modules\AppCouponCampaigns\Models;

use Illuminate\Database\Eloquent\Model;

class CouponRedemption extends Model
{
    protected $table = 'lb_coupon_redemptions';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'used_at' => 'datetime',
        ];
    }
}
