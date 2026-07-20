<?php

test('public FizaHUB docs page publishes the 22 endpoint cutover without legacy upload promises', function (): void {
    $html = $this->get('/api-fizahub')->assertOk()->getContent();

    expect($html)->toContain('MLHUB × FizaHUB Partner API')
        ->and($html)->toContain('22 endpoint')
        ->and($html)->toContain('15 màn hình')
        ->and($html)->toContain('/marketing-catalog')
        ->and($html)->toContain('/growth-insights')
        ->and($html)->toContain('/support-presets')
        ->and($html)->toContain('/crm-login-links')
        ->and(strtolower($html))->not->toContain('upload support attachment')
        ->and($html)->not->toContain('/attachments')
        ->and($html)->not->toContain('/integration-status')
        ->and($html)->not->toContain('/one-time-login')
        ->and($html)->not->toContain('test-fizahub-partner-token');
});

test('postman download returns one collection with five folders and exactly 22 requests', function (): void {
    $this->get('/api-fizahub/postman')
        ->assertOk()
        ->assertDownload('MLHUB-FizaHUB-Partner-API.postman_collection.json');

    $raw = (string) file_get_contents(base_path('modules/APIPartnerFizaHUB/docs/FizaHUB-Partner-API.postman_collection.json'));
    $json = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

    expect($json['item'] ?? [])->toHaveCount(5)
        ->and(collect($json['item'])->sum(fn (array $folder): int => count($folder['item'] ?? [])))->toBe(22)
        ->and($raw)->not->toContain('/attachments')
        ->and($raw)->not->toContain('test-fizahub-partner-token');
});

test('legacy extended download URL serves the same unified cutover collection', function (): void {
    $this->get('/api-fizahub/postman/extended')
        ->assertOk()
        ->assertDownload('MLHUB-FizaHUB-Partner-API.postman_collection.json');
});

test('public help page explains the sequential UI flow and dependency IDs without secrets', function (): void {
    $html = $this->get('/api-fizahub/help-test')->assertOk()->getContent();

    expect($html)->toContain('22 request')
        ->and($html)->toContain('onboarding_request_id')
        ->and($html)->toContain('campaign_id')
        ->and($html)->toContain('ticket_id')
        ->and($html)->toContain('Idempotency-Key')
        ->and($html)->toContain('text-only')
        ->and($html)->not->toContain('/attachments')
        ->and($html)->not->toContain('test-fizahub-partner-token');
});
