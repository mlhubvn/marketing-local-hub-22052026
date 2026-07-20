<?php

use Modules\APIPartnerFizaHUB\Models\PartnerApiLog;

require_once __DIR__.'/FizaHubTestHelpers.php';

function ssoHeaders(array $overrides = []): array
{
    return array_merge([
        'Authorization' => 'Bearer test-fizahub-partner-token',
        'X-Partner' => 'fizahub',
        'X-Request-Id' => (string) str()->uuid(),
        'Accept' => 'application/json',
    ], $overrides);
}

beforeEach(function (): void {
    config()->set('modules.apipartnerfizahub.token', 'test-fizahub-partner-token');
    config()->set('modules.apipartnerfizahub.rate_limit_per_minute', 60);
    config()->set('modules.apipartnerfizahub.timezone', 'Asia/Ho_Chi_Minh');
    bootFizaHubReadinessSchema();
});

afterEach(function (): void {
    dropFizaHubReadinessSchema();
});

test('sso verify confirms authentication and echoes partner code + server time without leaking the token', function (): void {
    $response = $this->postJson('/api/v1/partners/fizahub/partner/sso/verify', [], ssoHeaders())
        ->assertOk();

    $response->assertJsonPath('data.partner', 'fizahub')
        ->assertJsonPath('data.authenticated', true);

    expect($response->json('data.server_time'))->toBeString()->not->toBeEmpty();

    $encoded = json_encode($response->json());
    expect($encoded)->not->toContain('test-fizahub-partner-token');
});

test('sso verify rejects an invalid bearer token before reaching the controller', function (): void {
    $this->postJson('/api/v1/partners/fizahub/partner/sso/verify', [], ssoHeaders(['Authorization' => 'Bearer wrong-token']))
        ->assertUnauthorized()
        ->assertJsonPath('error.code', 'invalid_partner_token');
});

test('sso verify never mutates state and never logs the raw partner token', function (): void {
    $this->postJson('/api/v1/partners/fizahub/partner/sso/verify', [], ssoHeaders())->assertOk();

    $log = PartnerApiLog::query()->latest('id')->first();

    expect($log)->not->toBeNull();
    $encodedLog = json_encode($log->request_payload).json_encode($log->response_payload);
    expect($encodedLog)->not->toContain('test-fizahub-partner-token');
});
