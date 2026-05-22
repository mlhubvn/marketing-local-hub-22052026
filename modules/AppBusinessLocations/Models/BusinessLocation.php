<?php

namespace Modules\AppBusinessLocations\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class BusinessLocation extends Model
{
    protected $table = 'lb_locations';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'opening_hours' => 'array',
            'qr_design' => 'array',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'business_id');
    }

    public function qrTargetUrl(): string
    {
        if (Route::has('locations.public')) {
            return route('locations.public', $this);
        }

        return $this->destinationUrl();
    }

    public function destinationUrl(): string
    {
        return (string) ($this->google_maps_url ?: $this->business?->google_maps_url ?: $this->business?->website ?: url('/'));
    }
}
