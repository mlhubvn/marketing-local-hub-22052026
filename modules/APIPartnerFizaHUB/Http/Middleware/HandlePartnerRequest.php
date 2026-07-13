<?php

namespace Modules\APIPartnerFizaHUB\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Modules\APIPartnerFizaHUB\Models\PartnerApiLog;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;
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

        $idempotencyKey = trim((string) $request->headers->get('Idempotency-Key', ''));
        $requestHash = $this->requestHash($request);
        $log = null;

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
            // Routing pipeline normally converts exceptions via ExceptionHandler.
            // Keep a safety net for any exception that still bubbles here.
            report($exception);

            $response = PartnerApiResponse::error(
                'partner_api_error',
                'An unexpected partner API error occurred.',
                500,
                [],
                $requestId
            );
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
                'partner_api_error',
                'Partner API logging is not available.',
                500,
                [],
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

        $payload = is_array($existing->response_payload) ? $existing->response_payload : [];
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
        return hash('sha256', (string) $request->getContent());
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
