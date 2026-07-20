<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\APIPartnerFizaHUB\Http\Middleware\HandlePartnerRequest;
use Modules\APIPartnerFizaHUB\Http\Middleware\VerifyPartnerToken;
use Modules\APIPartnerFizaHUB\Models\PartnerApiLog;
use Modules\APIPartnerFizaHUB\Support\PartnerApiResponse;

require_once __DIR__.'/FizaHubTestHelpers.php';

function contractPartnerHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ], $overrides);
}

function contractWriteHeaders(string $idempotencyKey, array $overrides = []): array
{
    return contractPartnerHeaders(array_merge([
        'Idempotency-Key' => $idempotencyKey,
    ], $overrides));
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);

    bootFizaHubReadinessSchema();

    Route::middleware([
        'api',
        VerifyPartnerToken::class,
        HandlePartnerRequest::class,
    ])->post('api/v1/partners/fizahub/_contract/write', function (Request $request) {
        return PartnerApiResponse::success([
            'subject' => $request->string('subject')->toString(),
            'message' => $request->string('message')->toString(),
        ], 201);
    })->name('partner.fizahub._contract.write');
});

afterEach(function (): void {
    dropFizaHubReadinessSchema();
});

test('all partner errors preserve the canonical envelope and effective request id', function (): void {
    $requestId = (string) str()->uuid();

    $this->getJson(
        '/api/v1/partners/fizahub/businesses/missing/marketing-status',
        contractPartnerHeaders(['X-Request-Id' => $requestId])
    )->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('data', null)
        ->assertJsonPath('meta.request_id', $requestId)
        ->assertJsonPath('error.code', 'integration_not_found');
});

test('unmatched partner URLs still use the canonical error envelope', function (): void {
    $requestId = (string) str()->uuid();

    $this->postJson(
        '/api/v1/partners/fizahub/removed-route',
        [],
        contractPartnerHeaders(['X-Request-Id' => $requestId])
    )->assertNotFound()
        ->assertJsonPath('success', false)
        ->assertJsonPath('data', null)
        ->assertJsonPath('meta.request_id', $requestId)
        ->assertJsonPath('error.code', 'route_not_found');
});

test('every approved state changing route except SSO verify requires an idempotency key', function (string $method, string $uri): void {
    $this->json($method, $uri, [], contractPartnerHeaders())
        ->assertUnprocessable()
        ->assertJsonPath('error.code', 'validation_failed')
        ->assertJsonPath('error.details.Idempotency-Key.0', 'The Idempotency-Key header is required.');
})->with([
    ['POST', '/api/v1/partners/fizahub/onboarding-requests'],
    ['PATCH', '/api/v1/partners/fizahub/businesses/biz-1/profile'],
    ['PATCH', '/api/v1/partners/fizahub/businesses/biz-1/marketing-preferences'],
    ['POST', '/api/v1/partners/fizahub/businesses/biz-1/campaigns/campaign-1/approval'],
    ['POST', '/api/v1/partners/fizahub/businesses/biz-1/support-tickets'],
    ['POST', '/api/v1/partners/fizahub/businesses/biz-1/support-tickets/ticket-1/messages'],
    ['POST', '/api/v1/partners/fizahub/businesses/biz-1/support-tickets/ticket-1/close'],
    ['POST', '/api/v1/partners/fizahub/businesses/biz-1/support-tickets/ticket-1/reopen'],
    ['POST', '/api/v1/partners/fizahub/businesses/biz-1/crm-login-links'],
]);

test('read-only SSO verification does not require an idempotency key', function (): void {
    $this->postJson(
        '/api/v1/partners/fizahub/partner/sso/verify',
        [],
        contractPartnerHeaders()
    )->assertOk();
});

test('same idempotency key with reordered equivalent JSON replays but different data conflicts', function (): void {
    $key = 'idem-contract-001';
    $firstRequestId = (string) str()->uuid();
    $replayRequestId = (string) str()->uuid();

    $first = $this->postJson(
        '/api/v1/partners/fizahub/_contract/write',
        ['subject' => 'A', 'message' => 'B'],
        contractWriteHeaders($key, ['X-Request-Id' => $firstRequestId])
    )->assertCreated();

    $replay = $this->postJson(
        '/api/v1/partners/fizahub/_contract/write',
        ['message' => 'B', 'subject' => 'A'],
        contractWriteHeaders($key, ['X-Request-Id' => $replayRequestId])
    )->assertCreated();

    expect($replay->json('data'))->toBe($first->json('data'))
        ->and($replay->json('meta.request_id'))->toBe($replayRequestId)
        ->and(PartnerApiLog::query()->where('idempotency_key', $key)->count())->toBe(1);

    $this->postJson(
        '/api/v1/partners/fizahub/_contract/write',
        ['subject' => 'A', 'message' => 'Changed'],
        contractWriteHeaders($key)
    )->assertConflict()
        ->assertJsonPath('error.code', 'idempotency_conflict');
});

test('idempotency keys longer than the storage contract are rejected', function (): void {
    $this->postJson(
        '/api/v1/partners/fizahub/_contract/write',
        ['subject' => 'A', 'message' => 'B'],
        contractWriteHeaders(str_repeat('k', 129))
    )->assertUnprocessable()
        ->assertJsonPath('error.code', 'validation_failed');
});
