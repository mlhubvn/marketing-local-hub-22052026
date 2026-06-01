<?php

namespace Modules\AppLoyaltyStampCards\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class ReferralCampaign extends Model
{
    protected $table = 'lb_referral_campaigns';

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
        'settings' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'business_id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(ReferralLink::class, 'campaign_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class, 'campaign_id');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(ReferralReward::class, 'campaign_id');
    }

    public function publicUrl(): string
    {
        return route('referral-campaigns.public', ['campaign' => $this->slug]);
    }
}
