<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;

class HealthController
{
    public function __invoke(): JsonResponse
    {
        return PartnerApiResponse::success([
            'status' => 'ok',
            'partner' => 'fizahub',
            'api_version' => 'v1',
            'server_time' => now()->toIso8601String(),
        ]);
    }
}
