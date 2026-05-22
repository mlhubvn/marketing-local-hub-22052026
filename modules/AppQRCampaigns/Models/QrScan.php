<?php

namespace Modules\AppQRCampaigns\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrScan extends Model
{
    protected $table = 'lb_qr_scans';

    public $timestamps = false;

    protected $guarded = [];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(QrCampaign::class, 'campaign_id');
    }
}
