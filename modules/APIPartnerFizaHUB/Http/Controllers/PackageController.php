<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\APIPartnerFizaHUB\Services\PackageService;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;

class PackageController
{
    public function __construct(
        protected PackageService $packages
    ) {}

    public function show(string $external_business_id): JsonResponse
    {
        return PartnerApiResponse::success(
            $this->packages->forBusiness($external_business_id)
        );
    }
}
