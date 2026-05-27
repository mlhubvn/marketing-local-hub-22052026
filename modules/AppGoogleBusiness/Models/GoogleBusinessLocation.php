<?php

namespace Modules\AppGoogleBusiness\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class GoogleBusinessLocation extends Model
{
    protected $table = 'lb_google_business_locations';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'opening_hours' => 'array',
            'sync_business_info' => 'boolean',
            'sync_hours' => 'boolean',
            'sync_reviews' => 'boolean',
            'sync_insights' => 'boolean',
            'auto_reply_enabled' => 'boolean',
            'is_managed' => 'boolean',
            'managed_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'last_reviews_synced_at' => 'datetime',
            'last_info_synced_at' => 'datetime',
            'last_hours_synced_at' => 'datetime',
        ];
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(GoogleBusinessConnection::class, 'connection_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'business_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(GoogleReview::class, 'google_business_location_id');
    }
}
