<?php

namespace Modules\AppQRCampaigns\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppLandingPages\Models\LandingPage;

class QrCampaign extends Model
{
    protected $table = 'lb_campaigns';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'business_id');
    }

    public function scans(): HasMany
    {
        return $this->hasMany(QrScan::class, 'campaign_id');
    }

    public function landingPage(): HasOne
    {
        return $this->hasOne(LandingPage::class, 'campaign_id');
    }

    public function publicUrl(): string
    {
        $generator = 'Modules\\AppCustomDomain\\Support\\CustomDomainUrlGenerator';

        if (class_exists($generator)) {
            $url = app($generator)->campaignUrl($this, 'qr-campaigns.public', ['campaign' => $this->slug]);

            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        return route('qr-campaigns.public', ['campaign' => $this->slug]);
    }
}
