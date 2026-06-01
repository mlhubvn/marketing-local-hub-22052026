<?php

namespace Modules\AppLoyaltyStampCards\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AppCustomers\Models\Customer;

class ReferralReward extends Model
{
    protected $table = 'lb_referral_rewards';

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(ReferralCampaign::class, 'campaign_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function referral(): BelongsTo
    {
        return $this->belongsTo(Referral::class, 'referral_id');
    }
}
