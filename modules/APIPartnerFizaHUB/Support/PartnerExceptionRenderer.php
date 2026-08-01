<?php

namespace Modules\APIPartnerFizaHUB\Support;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Modules\AdminSupport\Models\SupportTicket;
use Modules\APIPartnerFizaHUB\Models\PartnerIntegration;
use Modules\APIPartnerFizaHUB\Models\PartnerOnboardingRequest;
use Modules\APIPartnerFizaHUB\Models\PartnerSupportAttachment;
use Modules\AppQRCampaigns\Models\QrCampaign;
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

        if ($exception instanceof ModelNotFoundException) {
            return $this->renderModelNotFound($exception);
        }

        if ($exception instanceof NotFoundHttpException) {
            // Laravel's Handler::prepareException() unconditionally rewraps every
            // ModelNotFoundException into a NotFoundHttpException BEFORE any renderable
            // callback (including this one) runs, discarding the original model info in
            // the process. The original exception survives as getPrevious(), so unwrap it
            // here — otherwise every not-found ever falls back to the generic
            // resource_not_found code and the specific *_not_found codes above are dead code.
            $previous = $exception->getPrevious();

            if ($previous instanceof ModelNotFoundException) {
                return $this->renderModelNotFound($previous);
            }

            return PartnerApiResponse::error(
                'route_not_found',
                __('Không tìm thấy API được yêu cầu.'),
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

        $this->logUnexpected($exception, $request);

        return PartnerApiResponse::error(
            'partner_api_error',
            'An unexpected partner API error occurred.',
            500
        );
    }

    /**
     * Map a not-found model to a specific, stable error.code plus a safe next_action, so
     * FizaHUB can branch its integration logic instead of treating every 404 the same way.
     */
    private function renderModelNotFound(ModelNotFoundException $exception): Response
    {
        $model = $exception->getModel();

        return match ($model) {
            PartnerOnboardingRequest::class => PartnerApiResponse::error(
                'onboarding_request_not_found',
                __('Không tìm thấy yêu cầu onboarding này.'),
                404,
                ['next_action' => 'create_onboarding_request']
            ),
            PartnerIntegration::class => PartnerApiResponse::error(
                'integration_not_found',
                __('Doanh nghiệp này chưa được liên kết với MKT.'),
                404,
                ['next_action' => 'create_onboarding_request']
            ),
            QrCampaign::class => PartnerApiResponse::error(
                'campaign_not_found',
                __('Không tìm thấy chiến dịch này.'),
                404,
                ['next_action' => 'list_campaigns_first']
            ),
            SupportTicket::class => PartnerApiResponse::error(
                'ticket_not_found',
                __('Không tìm thấy phiếu hỗ trợ này.'),
                404,
                ['next_action' => 'create_support_ticket']
            ),
            PartnerSupportAttachment::class => PartnerApiResponse::error(
                'attachment_not_found',
                __('Không tìm thấy tệp đính kèm này.'),
                404,
                ['next_action' => 'list_attachments']
            ),
            default => PartnerApiResponse::error(
                'resource_not_found',
                __('The requested resource was not found.'),
                404
            ),
        };
    }

    /**
     * Log an unexpected (unmapped) exception with partner correlation fields so it can be
     * found by request_id in production logs, without ever leaking payload/secret values.
     *
     * This is intentionally separate from Laravel's automatic ExceptionHandler::report()
     * (which already logs the raw exception/trace) — that entry has no request_id/endpoint
     * correlation, so this line exists purely to make incidents grep-able by request_id.
     */
    public function logUnexpected(Throwable $exception, Request $request): void
    {
        $requestId = (string) ($request->attributes->get('partner_request_id') ?? $request->headers->get('X-Request-Id', ''));

        Log::error('[fizahub-partner-api] unexpected exception', [
            'request_id' => $requestId,
            'method' => strtoupper($request->getMethod()),
            'endpoint' => '/'.$request->path(),
            'exception_class' => $exception::class,
            'exception_message' => $this->sanitizeMessage($exception->getMessage()),
        ]);
    }

    /**
     * Query exceptions embed raw SQL bindings (which may contain phone/email/tax-code
     * values) in their message. Strip that section and cap the length before logging.
     */
    private function sanitizeMessage(string $message): string
    {
        $bindingsPosition = stripos($message, '(Connection:');

        if ($bindingsPosition !== false) {
            $message = rtrim(substr($message, 0, $bindingsPosition));
        }

        return mb_substr($message, 0, 500);
    }

    private function isPartnerRequest(Request $request): bool
    {
        return $request->is('api/v1/partners/fizahub', 'api/v1/partners/fizahub/*');
    }
}
