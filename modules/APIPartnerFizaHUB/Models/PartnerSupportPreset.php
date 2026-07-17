<?php

namespace Modules\APIPartnerFizaHUB\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerSupportPreset extends Model
{
    protected $table = 'partner_support_presets';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'type_id' => 'integer',
            'required_fields' => 'array',
            'allowed_package_codes' => 'array',
            'sla_hours' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
