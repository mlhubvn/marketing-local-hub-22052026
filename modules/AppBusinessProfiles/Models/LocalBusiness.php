<?php

namespace Modules\AppBusinessProfiles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;
use Modules\AppQRCampaigns\Models\QrCampaign;

class LocalBusiness extends Model
{
    protected $table = 'lb_businesses';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'opening_hours' => 'array',
            'qr_design' => 'array',
        ];
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(QrCampaign::class, 'business_id');
    }

    public function qrTargetUrl(): string
    {
        if (Route::has('businesses.public')) {
            return route('businesses.public', $this);
        }

        return $this->destinationUrl();
    }

    public function destinationUrl(): string
    {
        return (string) ($this->google_maps_url ?: $this->website ?: url('/'));
    }
}
