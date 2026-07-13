<?php

namespace Modules\APIPartnerFizaHUB\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\AdminUser\Models\Team;
use Modules\AdminUser\Models\User;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class PartnerIntegration extends Model
{
    protected $table = 'partner_integrations';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'mlhub_user_id' => 'integer',
            'mlhub_workspace_id' => 'integer',
            'mlhub_business_id' => 'integer',
            'verification_status' => 'array',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mlhub_user_id');
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'mlhub_workspace_id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'mlhub_business_id');
    }

    public function oneTimeLogins(): HasMany
    {
        return $this->hasMany(PartnerOneTimeLogin::class, 'partner_integration_id');
    }

    public function onboardingRequests(): HasMany
    {
        return $this->hasMany(PartnerOnboardingRequest::class, 'external_business_id', 'external_business_id');
    }
}
