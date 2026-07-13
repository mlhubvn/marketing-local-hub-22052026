<?php

namespace Modules\APIPartnerFizaHUB\Models;

use Illuminate\Database\Eloquent\Model;

class PartnerApiLog extends Model
{
    protected $table = 'partner_api_logs';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'request_payload' => 'array',
            'response_payload' => 'array',
        ];
    }
}
