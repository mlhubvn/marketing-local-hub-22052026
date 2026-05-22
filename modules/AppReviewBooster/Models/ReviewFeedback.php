<?php

namespace Modules\AppReviewBooster\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AppQRCampaigns\Models\QrCampaign;

class ReviewFeedback extends Model
{
    protected $table = 'lb_review_feedbacks';

    protected $guarded = [];

    protected $casts = [
        'replied_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(QrCampaign::class, 'campaign_id');
    }
}
