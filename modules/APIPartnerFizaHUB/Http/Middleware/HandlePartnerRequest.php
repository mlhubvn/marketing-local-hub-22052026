<?php

namespace Modules\APIPartnerFizaHUB\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\APIPartnerFizaHUB\Models\PartnerApiLog;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;
use Modules\APIPartnerFizaHUB\Support\PartnerExceptionRenderer;
use Modules\APIPartnerFizaHUB\Support\PartnerPayloadRedactor;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class HandlePartnerRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = trim((string) $request->headers->get('X-Request-Id', ''));

        if ($requestId === '' || ! Str::isUuid($requestId)) {
            return PartnerApiResponse::error(
                'invalid_partner_header',
                'The X-Request-Id header must be a valid UUID.',
                400,
                [],
                $requestId !== '' ? $requestId : null
            );
        }

        $request->attributes->set('partner_request_id', $requestId);
        $request->headers->set('X-Request-Id', $requestId);

        // Partner API user-facing copy is Vietnamese for FizaHUB app screens.
        app()->setLocale((string) config('modules.apipartnerfizahub.locale', 'vi'));

        $idempotencyKey = trim((string) $request->headers->get('Idempotency-Key', ''));
        $requestHash = $this->requestHash($request);
        $log = null;

        if ($this->requiresIdempotency($request) && ($idempotencyKey === '' || strlen($idempotencyKey) > 128)) {
            $message = $idempotencyKey === ''
                ? 'The Idempotency-Key header is required.'
                : 'The Idempotency-Key header may not be greater than 128 characters.';
            $response = PartnerApiResponse::error(
                'validation_failed',
                __('Dữ liệu yêu cầu không hợp lệ.'),
                422,
                ['Idempotency-Key' => [$message]],
                $requestId
            );

            $this->finalizeLog($request, $response, $requestId, '', $requestHash, null);

            return $this->withRequestId($response, $requestId);
        }

        if ($idempotencyKey !== '') {
            $replayOrConflict = $this->beginIdempotentRequest($request, $requestId, $idempotencyKey, $requestHash);

            if ($replayOrConflict instanceof Response) {
                return $this->withRequestId($replayOrConflict, $requestId);
            }

            $log = $replayOrConflict;
        }

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            // Some exceptions may bubble past the routing pipeline; reuse partner renderer.
            $rendered = app(PartnerExceptionRenderer::class)->render($exception, $request);

            if ($rendered instanceof Response) {
                $response = $rendered;
            } else {
                report($exception);
                app(PartnerExceptionRenderer::class)->logUnexpected($exception, $request);

                $response = PartnerApiResponse::error(
                    'partner_api_error',
                    'An unexpected partner API error occurred.',
                    500,
                    [],
                    $requestId
                );
            }
        }

        $response = $this->withRequestId($response, $requestId);
        $this->finalizeLog($request, $response, $requestId, $idempotencyKey, $requestHash, $log);

        return $response;
    }

    private function beginIdempotentRequest(
        Request $request,
        string $requestId,
        string $idempotencyKey,
        string $requestHash
    ): Response|PartnerApiLog {
        if (! Schema::hasTable('partner_api_logs')) {
            return PartnerApiResponse::error(
                'partner_schema_not_ready',
                __('Hệ thống đối tác chưa sẵn sàng. Vui lòng thử lại sau ít phút.'),
                503,
                ['next_action' => 'retry_later'],
                $requestId
            );
        }

        try {
            return PartnerApiLog::query()->create([
                'partner_code' => (string) config('modules.apipartnerfizahub.partner_code', 'fizahub'),
                'method' => strtoupper($request->getMethod()),
                'endpoint' => '/'.$request->path(),
                'request_id' => $requestId,
                'idempotency_key' => $idempotencyKey,
                'request_hash' => $requestHash,
                'status_code' => 0,
                'request_payload' => [
                    '_request_hash' => $requestHash,
                ],
                'response_payload' => null,
            ]);
        } catch (Throwable $exception) {
            if (! $this->isUniqueConstraintViolation($exception)) {
                throw $exception;
            }
        }

        $existing = PartnerApiLog::query()
            ->where('partner_code', (string) config('modules.apipartnerfizahub.partner_code', 'fizahub'))
            ->where('method', strtoupper($request->getMethod()))
            ->where('endpoint', '/'.$request->path())
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if (! $existing) {
            return PartnerApiResponse::error(
                'idempotency_conflict',
                'An idempotency conflict occurred and the original request could not be loaded.',
                409,
                [],
                $requestId
            );
        }

        $storedHash = (string) ($existing->request_hash
            ?? data_get($existing->request_payload, '_request_hash')
            ?? '');

        if ($storedHash !== '' && ! hash_equals($storedHash, $requestHash)) {
            return PartnerApiResponse::error(
                'idempotency_conflict',
                'The Idempotency-Key was reused with a different request body.',
                409,
                [],
                $requestId
            );
        }

        if ((int) $existing->status_code === 0) {
            return PartnerApiResponse::error(
                'idempotency_in_progress',
                'A request with this Idempotency-Key is still in progress.',
                409,
                [],
                $requestId
            );
        }

        if ($request->route()?->getName() === 'partner.fizahub.businesses.crm-login-links.store') {
            return $existing;
        }

        $payload = is_array($existing->response_payload) ? $existing->response_payload : [];
        data_set($payload, 'meta.request_id', $requestId);
        $status = (int) $existing->status_code;

        return response()->json($payload, $status)->header('X-Request-Id', $requestId);
    }

    private function finalizeLog(
        Request $request,
        Response $response,
        string $requestId,
        string $idempotencyKey,
        string $requestHash,
        ?PartnerApiLog $log
    ): void {
        if (! Schema::hasTable('partner_api_logs')) {
            return;
        }

        $statusCode = $response->getStatusCode();
        $responsePayload = $this->decodeJsonResponse($response);
        $requestPayload = PartnerPayloadRedactor::redactAndCap([
            '_request_hash' => $requestHash,
            'headers' => [
                'authorization' => $request->headers->get('Authorization'),
                'x-partner' => $request->headers->get('X-Partner'),
                'x-request-id' => $requestId,
                'idempotency-key' => $idempotencyKey !== '' ? $idempotencyKey : null,
            ],
            'body' => $request->isJson() ? ($request->json()->all() ?: []) : $request->all(),
        ]);
        $redactedResponse = PartnerPayloadRedactor::redactAndCap($responsePayload);

        if ($log instanceof PartnerApiLog) {
            $log->forceFill([
                'request_id' => $requestId,
                'request_hash' => $requestHash,
                'status_code' => $statusCode,
                'request_payload' => $requestPayload,
                'response_payload' => $redactedResponse,
            ])->save();

            return;
        }

        PartnerApiLog::query()->create([
            'partner_code' => (string) config('modules.apipartnerfizahub.partner_code', 'fizahub'),
            'method' => strtoupper($request->getMethod()),
            'endpoint' => '/'.$request->path(),
            'request_id' => $requestId,
            'idempotency_key' => $idempotencyKey !== '' ? $idempotencyKey : null,
            'request_hash' => $requestHash,
            'status_code' => $statusCode,
            'request_payload' => $requestPayload,
            'response_payload' => $redactedResponse,
        ]);
    }

    private function requestHash(Request $request): string
    {
        $payload = $request->isJson() ? $request->json()->all() : $request->all();
        $canonical = [
            'query' => $this->canonicalize($request->query()),
            'body' => $this->canonicalize($payload),
        ];

        return hash('sha256', json_encode(
            $canonical,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        ));
    }

    private function requiresIdempotency(Request $request): bool
    {
        if ($request->route()?->getName() === 'partner.fizahub.sso.verify') {
            return false;
        }

        return in_array(strtoupper($request->method()), ['POST', 'PUT', 'PATCH', 'DELETE'], true);
    }

    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn (mixed $item): mixed => $this->canonicalize($item), $value);
        }

        ksort($value);

        return array_map(fn (mixed $item): mixed => $this->canonicalize($item), $value);
    }

    private function decodeJsonResponse(Response $response): array
    {
        $content = $response->getContent();

        if (! is_string($content) || $content === '') {
            return [];
        }

        $decoded = json_decode($content, true);

        return is_array($decoded) ? $decoded : ['raw' => '[non-json-response]'];
    }

    private function withRequestId(Response $response, string $requestId): Response
    {
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }

    private function isUniqueConstraintViolation(Throwable $exception): bool
    {
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'unique')
            || str_contains($message, 'duplicate')
            || (int) $exception->getCode() === 23000
            || (method_exists($exception, 'getPrevious')
                && $exception->getPrevious() instanceof Throwable
                && $this->isUniqueConstraintViolation($exception->getPrevious()));
    }
}
