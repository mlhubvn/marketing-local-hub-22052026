<?php

namespace Modules\AppGoogleBusiness\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class GoogleAutoReplyRule extends Model
{
    protected $table = 'lb_google_auto_reply_rules';

    protected $guarded = [];

    public function location(): BelongsTo
    {
        return $this->belongsTo(GoogleBusinessLocation::class, 'google_business_location_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'business_id');
    }
}
