<?php

namespace Modules\APIPartnerFizaHUB\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\AdminPlans\Models\AdminPlan;

class PartnerPackageAssignment extends Model
{
    protected $table = 'partner_package_assignments';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'partner_integration_id' => 'integer',
            'plan_id' => 'integer',
            'changed_by' => 'integer',
            'metadata' => 'array',
            'effective_from' => 'datetime',
            'effective_to' => 'datetime',
        ];
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(PartnerIntegration::class, 'partner_integration_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(AdminPlan::class, 'plan_id');
    }
}
