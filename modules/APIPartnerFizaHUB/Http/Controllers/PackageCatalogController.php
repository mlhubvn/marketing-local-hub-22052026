<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\APIPartnerFizaHUB\Services\PackageService;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;

class PackageCatalogController
{
    public function __construct(
        protected PackageService $packages
    ) {}

    public function index(): JsonResponse
    {
        return PartnerApiResponse::success(
            $this->packages->list()
        );
    }
}
