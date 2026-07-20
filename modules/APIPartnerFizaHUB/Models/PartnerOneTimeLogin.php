<?php

namespace Modules\APIPartnerFizaHUB\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AdminUser\Models\User;

class PartnerOneTimeLogin extends Model
{
    protected $table = 'partner_one_time_logins';

    protected $guarded = [];

    protected $hidden = [
        'token_hash',
        'token_ciphertext',
    ];

    protected function casts(): array
    {
        return [
            'partner_integration_id' => 'integer',
            'user_id' => 'integer',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(PartnerIntegration::class, 'partner_integration_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
