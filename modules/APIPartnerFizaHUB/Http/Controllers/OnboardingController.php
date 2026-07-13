<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\APIPartnerFizaHUB\Http\Requests\UpsertOnboardingRequest;
use Modules\APIPartnerFizaHUB\Services\OnboardingService;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;

class OnboardingController
{
    public function __construct(
        protected OnboardingService $onboarding
    ) {}

    public function store(UpsertOnboardingRequest $request): JsonResponse
    {
        $requestId = (string) $request->attributes->get(
            'partner_request_id',
            $request->headers->get('X-Request-Id')
        );

        $result = $this->onboarding->upsert($request->validated(), $requestId);
        $row = $result['onboarding'];
        $status = $this->httpStatus($row->status, (bool) $result['created']);

        return PartnerApiResponse::success(
            $this->onboarding->serialize($row),
            $status,
            $requestId
        );
    }

    public function show(Request $request, string $request_id): JsonResponse
    {
        $requestIdHeader = (string) $request->attributes->get(
            'partner_request_id',
            $request->headers->get('X-Request-Id')
        );

        $row = $this->onboarding->find($request_id);

        return PartnerApiResponse::success(
            $this->onboarding->serialize($row),
            200,
            $requestIdHeader
        );
    }

    private function httpStatus(string $status, bool $created): int
    {
        if (in_array($status, ['pending_verification', 'needs_review'], true)) {
            return 202;
        }

        return $created ? 201 : 200;
    }
}
