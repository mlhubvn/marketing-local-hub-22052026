<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\APIPartnerFizaHUB\Services\MarketingCatalogService;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;

class PackageCatalogController
{
    public function __construct(
        protected MarketingCatalogService $catalog
    ) {}

    public function index(Request $request): JsonResponse
    {
        return PartnerApiResponse::success(
            $this->catalog->catalog($request->query('industry'))
        );
    }
}
