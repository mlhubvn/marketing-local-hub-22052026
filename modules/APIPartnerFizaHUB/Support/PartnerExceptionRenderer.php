<?php

namespace Modules\APIPartnerFizaHUB\Support;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class PartnerExceptionRenderer
{
    public function render(Throwable $exception, Request $request): ?Response
    {
        if (! $this->isPartnerRequest($request)) {
            return null;
        }

        if ($exception instanceof ValidationException) {
            return PartnerApiResponse::error(
                'validation_failed',
                'The given data was invalid.',
                422,
                $exception->errors()
            );
        }

        if ($exception instanceof PartnerApiException) {
            return PartnerApiResponse::error(
                $exception->errorCode,
                $exception->getMessage(),
                $exception->status,
                $exception->details
            );
        }

        if ($exception instanceof InvalidArgumentException
            && str_contains($exception->getMessage(), 'onboarding status transition')) {
            return PartnerApiResponse::error(
                'invalid_status_transition',
                $exception->getMessage(),
                422
            );
        }

        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            return PartnerApiResponse::error(
                'resource_not_found',
                'The requested resource was not found.',
                404
            );
        }

        if ($exception instanceof TooManyRequestsHttpException) {
            return PartnerApiResponse::error(
                'rate_limit_exceeded',
                'Too many requests. Please retry later.',
                429
            );
        }

        if ($exception instanceof HttpResponseException) {
            $status = $exception->getResponse()->getStatusCode();

            if ($status === 429) {
                return PartnerApiResponse::error(
                    'rate_limit_exceeded',
                    'Too many requests. Please retry later.',
                    429
                );
            }

            return null;
        }

        return PartnerApiResponse::error(
            'partner_api_error',
            'An unexpected partner API error occurred.',
            500
        );
    }

    private function isPartnerRequest(Request $request): bool
    {
        return $request->is('api/v1/partners/fizahub', 'api/v1/partners/fizahub/*');
    }
}
