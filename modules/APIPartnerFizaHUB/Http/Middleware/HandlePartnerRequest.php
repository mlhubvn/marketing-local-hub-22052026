<?php

namespace Modules\APIPartnerFizaHUB\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
        // Computed once per request and reused by requestHash()/finalizeLog(): hashing every
        // uploaded file (up to 100MB) twice would double the CPU cost for no benefit.
        $fileFingerprints = $this->fileFingerprints($request->allFiles());
        $requestHash = $this->requestHash($request, $fileFingerprints);
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

            $this->finalizeLog($request, $response, $requestId, '', $requestHash, null, $fileFingerprints);

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
        $this->finalizeLog($request, $response, $requestId, $idempotencyKey, $requestHash, $log, $fileFingerprints);

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

    /**
     * @param  array<string, mixed>  $fileFingerprints
     */
    private function finalizeLog(
        Request $request,
        Response $response,
        string $requestId,
        string $idempotencyKey,
        string $requestHash,
        ?PartnerApiLog $log,
        array $fileFingerprints = []
    ): void {
        if (! Schema::hasTable('partner_api_logs')) {
            return;
        }

        $statusCode = $response->getStatusCode();
        $responsePayload = $this->decodeJsonResponse($response);
        // Use input() (not all()) so raw UploadedFile objects never leak into the JSON log —
        // they aren't JsonSerializable and would silently collapse to "{}", which was also the
        // root cause of the request-hash bug fixed alongside this: see fileFingerprints().
        $requestPayload = PartnerPayloadRedactor::redactAndCap(array_filter([
            '_request_hash' => $requestHash,
            'headers' => [
                'authorization' => $request->headers->get('Authorization'),
                'x-partner' => $request->headers->get('X-Partner'),
                'x-request-id' => $requestId,
                'idempotency-key' => $idempotencyKey !== '' ? $idempotencyKey : null,
            ],
            'body' => $request->isJson() ? ($request->json()->all() ?: []) : $request->input(),
            'files' => $fileFingerprints,
        ], static fn (mixed $value): bool => $value !== []));
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

    /**
     * @param  array<string, mixed>  $fileFingerprints
     */
    private function requestHash(Request $request, array $fileFingerprints = []): string
    {
        // input() (not all()) so multipart uploads (e.g. support attachments) don't merge raw
        // UploadedFile objects into $payload — see fileFingerprints() for why that mattered.
        $payload = $request->isJson() ? $request->json()->all() : $request->input();
        $canonical = [
            'query' => $this->canonicalize($request->query()),
            'body' => $this->canonicalize($payload),
            'files' => $fileFingerprints,
        ];

        return hash('sha256', json_encode(
            $canonical,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        ));
    }

    /**
     * Content fingerprint for uploaded files (name + size + sha256 of bytes) so the
     * Idempotency-Key hash actually distinguishes multipart uploads. Without this, every
     * UploadedFile object collapsed to "{}" when JSON-encoded (it isn't JsonSerializable and
     * exposes no public properties), so two DIFFERENT files sent under the SAME Idempotency-Key
     * hashed identically — the middleware would silently replay the first file's cached response
     * instead of returning `409 idempotency_conflict` for the second, mismatched file.
     *
     * @param  array<string, mixed>  $files
     * @return array<string, mixed>
     */
    private function fileFingerprints(array $files): array
    {
        $result = [];

        foreach ($files as $key => $file) {
            if (is_array($file)) {
                $result[$key] = $this->fileFingerprints($file);

                continue;
            }

            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $result[$key] = [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'sha256' => @hash_file('sha256', $file->getRealPath()) ?: null,
            ];
        }

        ksort($result);

        return $result;
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
