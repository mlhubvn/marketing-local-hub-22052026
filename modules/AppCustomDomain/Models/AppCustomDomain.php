<?php

namespace Modules\AppCustomDomain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AppCustomDomain extends Model
{
    protected $table = 'custom_domains';

    protected $fillable = [
        'owner_user_id',
        'team_id',
        'domain',
        'status',
        'is_default',
        'verification_token',
        'verified_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'owner_user_id' => 'integer',
            'team_id' => 'integer',
            'is_default' => 'boolean',
            'verified_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public static function verificationToken(): string
    {
        return 'qr-verify-'.Str::lower(Str::random(24));
    }
}
