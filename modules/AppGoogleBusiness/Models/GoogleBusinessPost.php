<?php

namespace Modules\AppGoogleBusiness\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppLandingPages\Models\LandingPage;
use Modules\AppQRCampaigns\Models\QrCampaign;

class GoogleBusinessPost extends Model
{
    protected $table = 'lb_google_business_posts';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(GoogleBusinessLocation::class, 'google_business_location_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'business_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(QrCampaign::class, 'campaign_id');
    }

    public function landingPage(): BelongsTo
    {
        return $this->belongsTo(LandingPage::class, 'landing_page_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(GoogleBusinessPostLog::class, 'post_id');
    }
}
