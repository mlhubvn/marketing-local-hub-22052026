<?php

test('public fizahub docs page is available without auth', function (): void {
    $response = $this->get('/api-fizahub');

    $response->assertOk();
    $html = $response->getContent();

    expect($html)->toContain('MLHUB × FizaHUB Partner API')
        ->and($html)->toContain(__('Partner API technical specification'))
        ->and($html)->toContain(__('Download Postman JSON'))
        ->and($html)->toContain(__('Step-by-step Postman test guide'))
        ->and($html)->toContain('/api-fizahub/help-test')
        ->and($html)->toContain(__('Spec overview'))
        ->and($html)->toContain(__('Endpoint overview'))
        ->and($html)->toContain(__('Technical architecture diagram'))
        ->and($html)->toContain(__('Operating flows'))
        ->and($html)->toContain('APIPartnerFizaHUB Adapter')
        ->and($html)->toContain(__('Health Check'))
        ->and($html)->toContain(__('Khởi tạo tài khoản'))
        ->and($html)->toContain(__('Dashboard tăng trưởng'))
        ->and($html)->toContain('One-time Login')
        ->and($html)->toContain(__('Bảng tra cứu tên hàm'))
        ->and($html)->toContain(__('Bảng tra cứu trường dữ liệu'))
        ->and($html)->toContain('?external_business_id=')
        ->and($html)->not->toContain('test-fizahub-partner-token')
        ->and($html)->not->toContain('FIZAHUB_PARTNER_TOKEN=')
        ->and($html)->not->toContain('sk_live');
});
test('postman collection download returns the documented filename', function (): void {
    $response = $this->get('/api-fizahub/postman');

    $response->assertOk()
        ->assertDownload('MLHUB-FizaHUB-Partner-API.postman_collection.json');

    $raw = file_get_contents(base_path('modules/APIPartnerFizaHUB/docs/FizaHUB-Partner-API.postman_collection.json'));
    $json = json_decode((string) $raw, true);

    expect($json)->toBeArray()
        ->and($json['info']['schema'] ?? null)
        ->toBe('https://schema.getpostman.com/json/collection/v2.1.0/collection.json')
        ->and($raw)->not->toContain('sk_live')
        ->and($raw)->not->toContain('test-fizahub-partner-token');
});

test('public fizahub help-test page guides postman step by step without secrets', function (): void {
    $response = $this->get('/api-fizahub/help-test');

    $response->assertOk();
    $html = $response->getContent();

    expect($html)->toContain(__('FizaHUB Postman step-by-step test guide'))
        ->and($html)->toContain(__('Install Postman and import the file'))
        ->and($html)->toContain(__('Fill in variables'))
        ->and($html)->toContain(__('Test GET Health'))
        ->and($html)->toContain(__('Test POST Onboarding'))
        ->and($html)->toContain(__('Test GET Package'))
        ->and($html)->toContain(__('Test POST One-time Login'))
        ->and($html)->toContain(__('Download Postman JSON'))
        ->and($html)->toContain('/api-fizahub/postman')
        ->and($html)->not->toContain('test-fizahub-partner-token')
        ->and($html)->not->toContain('FIZAHUB_PARTNER_TOKEN=')
        ->and($html)->not->toContain('sk_live');
});
