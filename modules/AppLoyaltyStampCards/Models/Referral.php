<?php

namespace Modules\AppLoyaltyStampCards\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AppCustomers\Models\Customer;

class Referral extends Model
{
    protected $table = 'lb_referrals';

    protected $guarded = [];

    protected $casts = [
        'converted_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(ReferralCampaign::class, 'campaign_id');
    }

    public function link(): BelongsTo
    {
        return $this->belongsTo(ReferralLink::class, 'referral_link_id');
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referrer_customer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'referred_customer_id');
    }
}
