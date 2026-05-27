<?php

namespace Modules\AppGoogleBusiness\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class GoogleReview extends Model
{
    protected $table = 'lb_google_reviews';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'review_created_at' => 'datetime',
            'review_updated_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    public function googleLocation(): BelongsTo
    {
        return $this->belongsTo(GoogleBusinessLocation::class, 'google_business_location_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'business_id');
    }
}
