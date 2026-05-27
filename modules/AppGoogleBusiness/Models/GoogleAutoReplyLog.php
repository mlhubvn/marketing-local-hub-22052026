<?php

namespace Modules\AppGoogleBusiness\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoogleAutoReplyLog extends Model
{
    protected $table = 'lb_google_auto_reply_logs';

    protected $guarded = [];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(GoogleAutoReplyRule::class, 'rule_id');
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(GoogleReview::class, 'review_id');
    }
}
