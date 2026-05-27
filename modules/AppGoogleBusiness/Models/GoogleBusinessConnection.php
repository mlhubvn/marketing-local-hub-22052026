<?php

namespace Modules\AppGoogleBusiness\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class GoogleBusinessConnection extends Model
{
    protected $table = 'lb_google_business_connections';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'scopes' => 'array',
            'auto_sync' => 'boolean',
            'expires_at' => 'datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    public function locations(): HasMany
    {
        return $this->hasMany(GoogleBusinessLocation::class, 'connection_id');
    }

    public function setAccessTokenAttribute(?string $value): void
    {
        $this->attributes['access_token'] = filled($value) ? Crypt::encryptString($value) : null;
    }

    public function getAccessTokenAttribute(?string $value): ?string
    {
        return filled($value) ? Crypt::decryptString($value) : null;
    }

    public function setRefreshTokenAttribute(?string $value): void
    {
        $this->attributes['refresh_token'] = filled($value) ? Crypt::encryptString($value) : null;
    }

    public function getRefreshTokenAttribute(?string $value): ?string
    {
        return filled($value) ? Crypt::decryptString($value) : null;
    }
}
