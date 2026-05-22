<?php

namespace Modules\AppLandingPages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AppBusinessProfiles\Models\LocalBusiness;
use Modules\AppQRCampaigns\Models\QrCampaign;

class LandingPage extends Model
{
    protected $table = 'lb_landing_pages';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'settings' => 'array',
            'published_at' => 'datetime',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'business_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(QrCampaign::class, 'campaign_id');
    }

    public function publicUrl(): string
    {
        $generator = 'Modules\\AppCustomDomain\\Support\\CustomDomainUrlGenerator';

        if (class_exists($generator)) {
            $url = app($generator)->landingPageUrl($this, 'landing-pages.public', ['landingPage' => $this->slug]);

            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        return route('landing-pages.public', ['landingPage' => $this->slug]);
    }

    public function qrUrl(): string
    {
        $generator = 'Modules\\AppCustomDomain\\Support\\CustomDomainUrlGenerator';

        if (class_exists($generator)) {
            $url = app($generator)->landingPageUrl($this, 'landing-pages.qr', ['landingPage' => $this->slug]);

            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        return route('landing-pages.qr', ['landingPage' => $this->slug]);
    }

    public function qrPngUrl(): string
    {
        $generator = 'Modules\\AppCustomDomain\\Support\\CustomDomainUrlGenerator';

        if (class_exists($generator)) {
            $url = app($generator)->landingPageUrl($this, 'landing-pages.qr.png', ['landingPage' => $this->slug]);

            if (is_string($url) && $url !== '') {
                return $url;
            }
        }

        return route('landing-pages.qr.png', ['landingPage' => $this->slug]);
    }
}
