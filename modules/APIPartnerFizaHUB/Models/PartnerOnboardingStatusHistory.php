<?php

namespace Modules\APIPartnerFizaHUB\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerOnboardingStatusHistory extends Model
{
    public $timestamps = false;

    protected $table = 'partner_onboarding_status_histories';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'onboarding_request_id' => 'integer',
            'changed_by_id' => 'integer',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function onboardingRequest(): BelongsTo
    {
        return $this->belongsTo(PartnerOnboardingRequest::class, 'onboarding_request_id');
    }
}
