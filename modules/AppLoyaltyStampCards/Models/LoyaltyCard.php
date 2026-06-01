<?php

namespace Modules\AppLoyaltyStampCards\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class LoyaltyCard extends Model
{
    protected $table = 'lb_loyalty_cards';

    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'business_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(LoyaltyCustomer::class, 'card_id');
    }

    public function stamps(): HasMany
    {
        return $this->hasMany(LoyaltyStamp::class, 'card_id');
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(LoyaltyReward::class, 'card_id');
    }

    public function publicUrl(): string
    {
        return route('loyalty-cards.public', ['card' => $this->slug]);
    }
}
