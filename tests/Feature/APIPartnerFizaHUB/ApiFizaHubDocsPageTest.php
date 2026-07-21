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

test('public docs keep the token off the HTML pages while the downloaded Postman ships it ready to run', function (): void {
    // Testing-phase choice (.env.example §8): the token is not printed on the HTML help pages
    // (to avoid casual scraping), but the downloaded Postman collection has it prefilled so the
    // FizaHUB dev can run every request immediately.
    config()->set('app.url', 'https://mlhub.vn');
    config()->set('modules.apipartnerfizahub.token', 'fizahub-ready-to-run-test-token');

    $this->get('/api-fizahub')
        ->assertOk()
        ->assertSee('https://mlhub.vn/api/v1/partners/fizahub', false)
        ->assertDontSee('fizahub-ready-to-run-test-token', false)
        ->assertSee('Tải về là chạy được ngay', false);

    $this->get('/api-fizahub/help-test')
        ->assertOk()
        ->assertSee('https://mlhub.vn', false)
        ->assertDontSee('fizahub-ready-to-run-test-token', false)
        ->assertSee('partner_token', false);

    $response = $this->get('/api-fizahub/postman')->assertOk();
    $baseResponse = $response->baseResponse;
    $content = $baseResponse instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse
        ? (string) file_get_contents($baseResponse->getFile()->getPathname())
        : (string) $response->getContent();
    $collection = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    $variables = collect($collection['variable'] ?? [])->pluck('value', 'key');

    expect($variables->get('base_url'))->toBe('https://mlhub.vn')
        ->and($variables->get('partner_token'))->toBe('fizahub-ready-to-run-test-token');
});

test('public help page explains the sequential UI flow and dependency IDs without secrets', function (): void {
    $html = $this->get('/api-fizahub/help-test')->assertOk()->getContent();

    expect($html)->toContain('22 request')
        ->and($html)->toContain('onboarding_request_id')
        ->and($html)->toContain('campaign_id')
        ->and($html)->toContain('ticket_id')
        ->and($html)->toContain('Idempotency-Key')
        ->and($html)->toContain('text-only')
        ->and($html)->toContain('Collection Runner')
        ->and($html)->toContain('Examples')
        ->and($html)->toContain('marketing_goal_codes')
        ->and($html)->not->toContain('/attachments')
        ->and($html)->not->toContain('test-fizahub-partner-token');
});
