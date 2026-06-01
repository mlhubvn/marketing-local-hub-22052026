<?php

namespace Modules\AppLoyaltyStampCards\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AppCustomers\Models\Customer;

class ReferralLink extends Model
{
    protected $table = 'lb_referral_links';

    protected $guarded = [];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(ReferralCampaign::class, 'campaign_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function publicUrl(): string
    {
        return route('referral-links.public', ['link' => $this->code]);
    }
}
