<?php

namespace Modules\AppEmailAutomation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

class EmailTemplate extends Model
{
    protected $table = 'lb_email_templates';

    protected $guarded = [];

    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(LocalBusiness::class, 'business_id');
    }
}
