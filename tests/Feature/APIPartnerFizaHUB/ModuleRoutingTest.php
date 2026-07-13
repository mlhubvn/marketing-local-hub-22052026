<?php

use Illuminate\Support\Facades\Route;
use Modules\APIPartnerFizaHUB\Providers\APIPartnerFizaHUBServiceProvider;

function fizahubPartnerHeaders(array $overrides = []): array
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
});

test('APIPartnerFizaHUB service provider is registered', function (): void {
    expect(app()->providerIsLoaded(APIPartnerFizaHUBServiceProvider::class))->toBeTrue();
});

test('partner fizahub health route is registered', function (): void {
    expect(Route::has('partner.fizahub.health'))->toBeTrue();
});

test('partner fizahub health returns fixed JSON contract', function (): void {
    $requestId = '018f5a64-b40b-7f60-a925-dea047cf6590';

    $response = $this->getJson('/api/v1/partners/fizahub/health', fizahubPartnerHeaders([
        'X-Request-Id' => $requestId,
    ]));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/json')
        ->assertHeader('X-Request-Id', $requestId)
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'ok')
        ->assertJsonPath('data.partner', 'fizahub')
        ->assertJsonPath('data.api_version', 'v1')
        ->assertJsonPath('meta.request_id', $requestId)
        ->assertJsonPath('error', null);

    expect($response->json('data.server_time'))->toBeString()->not->toBeEmpty();
});
