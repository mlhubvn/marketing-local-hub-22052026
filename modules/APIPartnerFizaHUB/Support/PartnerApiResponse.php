<?php

namespace Modules\APIPartnerFizaHUB\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PartnerApiResponse
{
    public static function success(mixed $data = [], int $status = 200, ?string $requestId = null): JsonResponse
    {
        $requestId = self::resolveRequestId($requestId);

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => [
                'request_id' => $requestId,
            ],
            'error' => null,
        ], $status)->header('X-Request-Id', $requestId);
    }

    public static function error(
        string $code,
        string $message,
        int $status = 400,
        array $details = [],
        ?string $requestId = null
    ): JsonResponse {
        $requestId = self::resolveRequestId($requestId);

        return response()->json([
            'success' => false,
            'data' => null,
            'meta' => [
                'request_id' => $requestId,
            ],
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $status)->header('X-Request-Id', $requestId);
    }

    public static function resolveRequestId(?string $requestId = null): string
    {
        if (is_string($requestId) && $requestId !== '') {
            return $requestId;
        }

        /** @var Request|null $request */
        $request = request();

        if ($request instanceof Request) {
            $fromAttribute = $request->attributes->get('partner_request_id');
            if (is_string($fromAttribute) && $fromAttribute !== '') {
                return $fromAttribute;
            }

            $fromHeader = $request->headers->get('X-Request-Id');
            if (is_string($fromHeader) && $fromHeader !== '') {
                return $fromHeader;
            }
        }

        return '';
    }
}
