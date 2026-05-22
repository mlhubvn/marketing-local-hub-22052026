<?php

namespace Modules\AppLeadForms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AppQRCampaigns\Models\QrCampaign;

class LeadSubmission extends Model
{
    protected $table = 'lb_lead_submissions';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(QrCampaign::class, 'campaign_id');
    }
}
