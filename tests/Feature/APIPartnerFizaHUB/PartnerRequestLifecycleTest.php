<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Modules\APIPartnerFizaHUB\Http\Middleware\HandlePartnerRequest;
use Modules\APIPartnerFizaHUB\Http\Middleware\VerifyPartnerToken;
use Modules\APIPartnerFizaHUB\Models\PartnerApiLog;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;
use Modules\APIPartnerFizaHUB\Support\PartnerPayloadRedactor;

require_once __DIR__.'/FizaHubTestHelpers.php';

function createPartnerLifecycleTables(): void
{
    Schema::dropIfExists('partner_api_logs');

    Schema::create('partner_api_logs', function (Blueprint $table): void {
        $table->id();
        $table->string('partner_code', 32);
        $table->string('method', 16);
        $table->string('endpoint', 255);
        $table->uuid('request_id')->nullable();
        $table->string('idempotency_key', 128)->nullable();
        $table->string('request_hash', 64)->nullable();
        $table->unsignedInteger('status_code')->default(0);
        $table->json('request_payload')->nullable();
        $table->json('response_payload')->nullable();
        $table->timestamps();

        $table->unique(
            ['partner_code', 'method', 'endpoint', 'idempotency_key'],
            'partner_api_logs_idempotency_unique'
        );
    });
}

function lifecycleHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ], $overrides);
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);

    bootFizaHubReadinessSchema();
    createPartnerLifecycleTables();

    Route::middleware([
        'api',
        VerifyPartnerToken::class,
        HandlePartnerRequest::class,
        'throttle:fizahub-partner',
    ])
        ->prefix('api/v1/partners/fizahub')
        ->group(function (): void {
            Route::post('_lifecycle/echo', function (Request $request) {
                return PartnerApiResponse::success([
                    'echo' => [
                        'hello' => $request->input('hello'),
                    ],
                    'note' => 'ok',
                ], 201);
            });

            Route::post('_lifecycle/secrets', function (): JsonResponse {
                return PartnerApiResponse::success([
                    'password' => 'should-not-persist',
                    'token' => 'should-not-persist',
                    'url' => 'https://mlhub.vn/partner/login?token=secret-login-token',
                ], 201);
            });

            Route::post('_lifecycle/identity-docs', function (Request $request): JsonResponse {
                return PartnerApiResponse::success([
                    'received' => true,
                    'keys' => array_keys($request->all()),
                ], 201);
            });

            Route::post('_lifecycle/fail', function (): void {
                throw new RuntimeException('forced partner failure');
            });

            Route::post('_lifecycle/validate', function (): void {
                throw ValidationException::withMessages([
                    'subject' => ['The subject field is required.'],
                ]);
            });

            Route::post('_lifecycle/upload', function (Request $request): JsonResponse {
                $file = $request->file('file');

                return PartnerApiResponse::success([
                    'original_name' => $file?->getClientOriginalName(),
                    'sha256' => $file ? hash_file('sha256', $file->getRealPath()) : null,
                ], 201);
            });
        });
});

afterEach(function (): void {
    Schema::dropIfExists('partner_api_logs');
    dropFizaHubReadinessSchema();
});

test('GET health requests are written to partner api logs', function (): void {
    $requestId = (string) str()->uuid();

    $this->getJson('/api/v1/partners/fizahub/health', lifecycleHeaders([
        'X-Request-Id' => $requestId,
    ]))->assertOk();

    $log = PartnerApiLog::query()->where('request_id', $requestId)->first();

    expect($log)->not->toBeNull()
        ->and($log->method)->toBe('GET')
        ->and($log->endpoint)->toBe('/api/v1/partners/fizahub/health')
        ->and($log->status_code)->toBe(200)
        ->and($log->response_payload['success'] ?? null)->toBeTrue();
});

test('POST with idempotency key reserves then replays completed response', function (): void {
    $idempotencyKey = (string) str()->uuid();
    $payload = ['hello' => 'world', 'password' => 'secret', 'token' => 'abc'];

    $first = $this->postJson(
        '/api/v1/partners/fizahub/_lifecycle/echo',
        $payload,
        lifecycleHeaders(['Idempotency-Key' => $idempotencyKey])
    )->assertCreated();

    $second = $this->postJson(
        '/api/v1/partners/fizahub/_lifecycle/echo',
        $payload,
        lifecycleHeaders(['Idempotency-Key' => $idempotencyKey])
    )->assertCreated();

    expect($second->json('data'))->toBe($first->json('data'))
        ->and($second->json('success'))->toBeTrue()
        ->and(PartnerApiLog::query()->where('idempotency_key', $idempotencyKey)->count())->toBe(1);

    $log = PartnerApiLog::query()->where('idempotency_key', $idempotencyKey)->firstOrFail();
    $encodedRequest = json_encode($log->request_payload);

    expect($encodedRequest)->not->toContain('secret')
        ->and($encodedRequest)->not->toContain('"abc"');
});

test('same idempotency key with different body returns conflict', function (): void {
    $idempotencyKey = (string) str()->uuid();

    $this->postJson(
        '/api/v1/partners/fizahub/_lifecycle/echo',
        ['value' => 1],
        lifecycleHeaders(['Idempotency-Key' => $idempotencyKey])
    )->assertCreated();

    $this->postJson(
        '/api/v1/partners/fizahub/_lifecycle/echo',
        ['value' => 2],
        lifecycleHeaders(['Idempotency-Key' => $idempotencyKey])
    )
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'idempotency_conflict');
});

test('retrying a multipart upload with the same file under the same idempotency key replays the cached response', function (): void {
    $idempotencyKey = (string) str()->uuid();
    $headers = lifecycleHeaders(['Idempotency-Key' => $idempotencyKey]);
    unset($headers['Content-Type']);

    $makeFile = fn () => UploadedFile::fake()->createWithContent('a.txt', 'same file bytes');

    $first = $this->post('/api/v1/partners/fizahub/_lifecycle/upload', ['file' => $makeFile()], $headers)
        ->assertCreated();

    $second = $this->post('/api/v1/partners/fizahub/_lifecycle/upload', ['file' => $makeFile()], $headers)
        ->assertCreated();

    expect($second->json('data'))->toBe($first->json('data'))
        ->and(PartnerApiLog::query()->where('idempotency_key', $idempotencyKey)->count())->toBe(1);
});

test('reusing an idempotency key with a DIFFERENT uploaded file returns a conflict instead of silently replaying the first file', function (): void {
    // Regression test: HandlePartnerRequest::requestHash() used to hash $request->all(), and
    // an UploadedFile JSON-encodes to "{}" (no public properties) — so two different files sent
    // under the same Idempotency-Key hashed identically and the second file's upload was never
    // actually processed, the partner just silently got back the first file's cached response.
    $idempotencyKey = (string) str()->uuid();
    $headers = lifecycleHeaders(['Idempotency-Key' => $idempotencyKey]);
    unset($headers['Content-Type']);

    $this->post('/api/v1/partners/fizahub/_lifecycle/upload', [
        'file' => UploadedFile::fake()->createWithContent('a.txt', 'file A content'),
    ], $headers)->assertCreated();

    $this->post('/api/v1/partners/fizahub/_lifecycle/upload', [
        'file' => UploadedFile::fake()->createWithContent('b.txt', 'a totally different file B content'),
    ], $headers)
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'idempotency_conflict');
});

test('in progress idempotency key returns conflict', function (): void {
    $idempotencyKey = (string) str()->uuid();
    $body = json_encode(['pending' => true], JSON_THROW_ON_ERROR);
    $hash = hash('sha256', json_encode([
        'query' => [],
        'body' => ['pending' => true],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

    PartnerApiLog::query()->create([
        'partner_code' => 'fizahub',
        'method' => 'POST',
        'endpoint' => '/api/v1/partners/fizahub/_lifecycle/echo',
        'request_id' => (string) str()->uuid(),
        'idempotency_key' => $idempotencyKey,
        'request_hash' => $hash,
        'status_code' => 0,
        'request_payload' => ['_request_hash' => $hash],
        'response_payload' => null,
    ]);

    $this->call(
        'POST',
        '/api/v1/partners/fizahub/_lifecycle/echo',
        [],
        [],
        [],
        [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer test-fizahub-partner-token',
            'HTTP_X_PARTNER' => 'fizahub',
            'HTTP_X_REQUEST_ID' => (string) str()->uuid(),
            'HTTP_IDEMPOTENCY_KEY' => $idempotencyKey,
        ],
        $body
    )
        ->assertStatus(409)
        ->assertJsonPath('error.code', 'idempotency_in_progress');
});

test('unexpected exceptions are logged as partner_api_error without leaking internals', function (): void {
    $requestId = (string) str()->uuid();

    $response = $this->postJson(
        '/api/v1/partners/fizahub/_lifecycle/fail',
        ['password' => 'super-secret'],
        lifecycleHeaders([
            'X-Request-Id' => $requestId,
            'Idempotency-Key' => (string) str()->uuid(),
        ])
    );

    $response->assertStatus(500)
        ->assertJsonPath('error.code', 'partner_api_error')
        ->assertJsonMissing(['forced partner failure']);

    $log = PartnerApiLog::query()->where('request_id', $requestId)->first();

    expect($log)->not->toBeNull()
        ->and($log->status_code)->toBe(500)
        ->and(json_encode($log->request_payload))->not->toContain('super-secret')
        ->and(json_encode($log->response_payload))->not->toContain('forced partner failure');
});

test('validation exceptions return 422 partner JSON', function (): void {
    $this->postJson(
        '/api/v1/partners/fizahub/_lifecycle/validate',
        [],
        lifecycleHeaders(['Idempotency-Key' => (string) str()->uuid()])
    )
        ->assertStatus(422)
        ->assertJsonPath('error.code', 'validation_failed')
        ->assertJsonPath('error.details.subject.0', 'The subject field is required.');
});

test('payload redactor recursively redacts sensitive keys and caps oversized payloads', function (): void {
    $payload = [
        'owner' => [
            'password' => 'secret',
            'authorization' => 'Bearer abc',
            'token' => 'tok',
            'nested' => [
                'cccd' => '012345678901',
                'identity_document' => 'file.bin',
                'business_license_file' => 'license.pdf',
                'url' => 'https://mlhub.vn/login?token=one-time',
            ],
        ],
        'safe' => 'ok',
    ];

    $redacted = PartnerPayloadRedactor::redact($payload);

    expect($redacted['owner']['password'])->toBe('[REDACTED]')
        ->and($redacted['owner']['authorization'])->toBe('[REDACTED]')
        ->and($redacted['owner']['token'])->toBe('[REDACTED]')
        ->and($redacted['owner']['nested']['cccd'])->toBe('[REDACTED]')
        ->and($redacted['owner']['nested']['identity_document'])->toBe('[REDACTED]')
        ->and($redacted['owner']['nested']['business_license_file'])->toBe('[REDACTED]')
        ->and($redacted['owner']['nested']['url'])->toBe('[REDACTED]')
        ->and($redacted['safe'])->toBe('ok');

    $identityPayload = [
        'owner' => [
            'nested' => [
                'identity_card' => 'card-raw',
                'identity_image' => 'face-raw.bin',
                'business_license_image' => 'gpkd-raw.bin',
            ],
        ],
    ];
    $identityRedacted = PartnerPayloadRedactor::redact($identityPayload);
    expect($identityRedacted['owner']['nested']['identity_card'])->toBe('[REDACTED]')
        ->and($identityRedacted['owner']['nested']['identity_image'])->toBe('[REDACTED]')
        ->and($identityRedacted['owner']['nested']['business_license_image'])->toBe('[REDACTED]');

    $oversized = ['blob' => str_repeat('x', 70000)];
    $capped = PartnerPayloadRedactor::cap($oversized);

    expect($capped)->toHaveKeys(['truncated', 'sha256'])
        ->and($capped['truncated'])->toBeTrue();
});

test('logged response redacts one-time login url values', function (): void {
    $requestId = (string) str()->uuid();

    $this->postJson(
        '/api/v1/partners/fizahub/_lifecycle/secrets',
        ['note' => 'login'],
        lifecycleHeaders([
            'X-Request-Id' => $requestId,
            'Idempotency-Key' => (string) str()->uuid(),
        ])
    )->assertCreated();

    $log = PartnerApiLog::query()->where('request_id', $requestId)->firstOrFail();
    $encoded = json_encode($log->response_payload);

    expect($encoded)->not->toContain('secret-login-token')
        ->and($encoded)->not->toContain('should-not-persist')
        ->and(data_get($log->response_payload, 'data.url'))->toBe('[REDACTED]');
});

test('partner api logs redact nested identity image fields', function (): void {
    $requestId = (string) str()->uuid();

    $this->postJson(
        '/api/v1/partners/fizahub/_lifecycle/identity-docs',
        [
            'owner' => [
                'name' => 'Safe Name',
                'identity_image' => 'raw-face-bytes',
                'docs' => [
                    'identity_card' => 'raw-card',
                    'business_license_image' => 'raw-gpkd-image',
                    'business_license_file' => 'raw-gpkd-file',
                    'identity_document' => 'raw-identity-doc',
                    'cccd' => '012345678901',
                ],
            ],
        ],
        lifecycleHeaders([
            'X-Request-Id' => $requestId,
            'Idempotency-Key' => (string) str()->uuid(),
        ])
    )->assertCreated();

    $log = PartnerApiLog::query()->where('request_id', $requestId)->firstOrFail();
    $encoded = json_encode($log->request_payload);

    expect($encoded)->not->toContain('raw-face-bytes')
        ->and($encoded)->not->toContain('raw-card')
        ->and($encoded)->not->toContain('raw-gpkd-image')
        ->and($encoded)->not->toContain('raw-gpkd-file')
        ->and($encoded)->not->toContain('raw-identity-doc')
        ->and($encoded)->not->toContain('012345678901')
        ->and(data_get($log->request_payload, 'body.owner.identity_image'))->toBe('[REDACTED]')
        ->and(data_get($log->request_payload, 'body.owner.docs.business_license_image'))->toBe('[REDACTED]')
        ->and(data_get($log->request_payload, 'body.owner.name'))->toBe('Safe Name');
});
