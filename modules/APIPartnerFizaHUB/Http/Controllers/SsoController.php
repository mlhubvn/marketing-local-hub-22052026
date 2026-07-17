<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\APIPartnerFizaHUB\Services\PartnerMappingService;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;

class SsoController
{
    public function __construct(
        protected PartnerMappingService $mapping
    ) {}

    /**
     * Confirms the partner bearer token and X-Partner header are valid.
     * VerifyPartnerToken middleware has already authenticated the request.
     */
    public function verify(): JsonResponse
    {
        $timezone = (string) config('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');

        return PartnerApiResponse::success([
            'partner' => $this->mapping->partnerCode(),
            'authenticated' => true,
            'server_time' => now($timezone)->toIso8601String(),
        ]);
    }
}
