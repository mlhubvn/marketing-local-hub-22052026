<?php

use Illuminate\Support\Facades\RateLimiter;

require_once __DIR__.'/FizaHubTestHelpers.php';

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);
    RateLimiter::clear('fizahub|127.0.0.1');

    bootFizaHubReadinessSchema();
});

afterEach(function (): void {
    dropFizaHubReadinessSchema();
});

function partnerAuthHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Accept' => 'application/json',
    ], $overrides);
}

test('missing bearer token returns 401 partner JSON', function (): void {
    $requestId = (string) str()->uuid();

    $this->getJson('/api/v1/partners/fizahub/health', partnerAuthHeaders([
        'Authorization' => '',
        'X-Request-Id' => $requestId,
    ]))
        ->assertUnauthorized()
        ->assertHeader('X-Request-Id', $requestId)
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'invalid_partner_token');
});

test('incorrect bearer token returns 401 partner JSON', function (): void {
    $this->getJson('/api/v1/partners/fizahub/health', partnerAuthHeaders([
        'Authorization' => 'Bearer wrong-token',
    ]))
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'invalid_partner_token');
});

test('empty configured partner token is unauthorized', function (): void {
    config()->set('modules.apipartnerfizahub.token', '');

    $this->getJson('/api/v1/partners/fizahub/health', partnerAuthHeaders())
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'invalid_partner_token');
});

test('wrong X-Partner header returns 400 partner JSON', function (): void {
    $this->getJson('/api/v1/partners/fizahub/health', partnerAuthHeaders([
        'X-Partner' => 'other',
    ]))
        ->assertStatus(400)
        ->assertJsonPath('error.code', 'invalid_partner_header');
});

test('missing X-Request-Id returns 400 partner JSON', function (): void {
    $this->getJson('/api/v1/partners/fizahub/health', partnerAuthHeaders([
        'X-Request-Id' => '',
    ]))
        ->assertStatus(400)
        ->assertJsonPath('error.code', 'invalid_partner_header');
});

test('non UUID X-Request-Id returns 400 partner JSON', function (): void {
    $this->getJson('/api/v1/partners/fizahub/health', partnerAuthHeaders([
        'X-Request-Id' => 'not-a-uuid',
    ]))
        ->assertStatus(400)
        ->assertJsonPath('error.code', 'invalid_partner_header');
});

test('valid partner headers return health 200 and echo request id', function (): void {
    $requestId = '018f5a64-b40b-7f60-a925-dea047cf6590';

    $this->getJson('/api/v1/partners/fizahub/health', partnerAuthHeaders([
        'X-Request-Id' => $requestId,
    ]))
        ->assertOk()
        ->assertHeader('X-Request-Id', $requestId)
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.request_id', $requestId)
        ->assertJsonPath('error', null);
});

test('rate limiter exhaustion returns 429 JSON not HTML', function (): void {
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 1);
    RateLimiter::clear('fizahub|127.0.0.1');

    $this->getJson('/api/v1/partners/fizahub/health', partnerAuthHeaders())->assertOk();

    $response = $this->getJson('/api/v1/partners/fizahub/health', partnerAuthHeaders());

    $response->assertStatus(429)
        ->assertHeader('Content-Type', 'application/json')
        ->assertJsonPath('success', false)
        ->assertJsonPath('error.code', 'rate_limit_exceeded');

    expect($response->headers->get('Content-Type'))->toContain('application/json');
    expect($response->getContent())->not->toContain('<html');
});
