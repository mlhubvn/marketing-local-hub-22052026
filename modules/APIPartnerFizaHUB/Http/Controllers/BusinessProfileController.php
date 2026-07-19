<?php

namespace Modules\APIPartnerFizaHUB\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\APIPartnerFizaHUB\Http\Requests\UpdateBusinessProfileRequest;
use Modules\APIPartnerFizaHUB\Services\IntegrationProfileService;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;

class BusinessProfileController
{
    public function __construct(
        protected IntegrationProfileService $profiles
    ) {}

    public function status(string $external_business_id): JsonResponse
    {
        return PartnerApiResponse::success(
            $this->profiles->status($external_business_id)
        );
    }

    public function update(UpdateBusinessProfileRequest $request, string $external_business_id): JsonResponse
    {
        $result = $this->profiles->updateProfile($external_business_id, $request->validated());

        if ($request->attributes->get('used_deprecated_flat_profile_body') === true) {
            $result['deprecation_notice'] = __('Gửi name/phone/address ở cấp cao nhất đã lỗi thời. Vui lòng dùng "business": { "name", "phone", "address" }.');
        }

        return PartnerApiResponse::success($result);
    }
}
