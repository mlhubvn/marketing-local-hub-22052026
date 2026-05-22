<?php

namespace Modules\AppFeedbackForms\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AppQRCampaigns\Models\QrCampaign;

class FeedbackResponse extends Model
{
    protected $table = 'lb_feedback_responses';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['payload' => 'array', 'resolved_at' => 'datetime'];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(QrCampaign::class, 'campaign_id');
    }
}
