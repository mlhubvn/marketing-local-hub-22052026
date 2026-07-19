<?php

test('public fizahub docs page is available without auth', function (): void {
    $response = $this->get('/api-fizahub');

    $response->assertOk();
    $html = $response->getContent();

    expect($html)->toContain('MLHUB × FizaHUB Partner API')
        ->and($html)->toContain(__('Tổng quan'))
        ->and($html)->toContain(__('Bảng trạng thái tiếng Việt'))
        ->and($html)->toContain(__('Chờ tư vấn viên liên hệ'))
        ->and($html)->toContain(__('Cần kiểm tra'))
        ->and($html)->toContain(__('Sẵn sàng sử dụng'))
        ->and($html)->toContain(__('Hoàn tất'))
        ->and($html)->toContain(__('Bảng tra cứu tên hàm'))
        ->and($html)->toContain(__('Bảng tra cứu trường dữ liệu'))
        ->and($html)->toContain(__('external_business_id là khóa kỹ thuật chính giữa FizaHUB và MLHUB.'))
        ->and($html)->toContain(__('Support detail/message bắt buộc có query external_business_id.'))
        ->and($html)->toContain('?external_business_id=')
        ->and($html)->toContain(__('Download Postman JSON'))
        ->and($html)->toContain('/api-fizahub/help-test')
        ->and($html)->toContain('rel="icon"')
        ->and($html)->toContain('fonts.bunny.net')
        ->and($html)->not->toContain('/favicon.ico')
        ->and($html)->not->toContain('test-fizahub-partner-token')
        ->and($html)->not->toContain('FIZAHUB_PARTNER_TOKEN=')
        ->and($html)->not->toContain('sk_live');
});

test('postman MVP collection download returns the documented legacy filename', function (): void {
    $response = $this->get('/api-fizahub/postman');

    $response->assertOk()
        ->assertDownload('MLHUB-FizaHUB-Partner-API.postman_collection.json');

    $raw = file_get_contents(base_path('modules/APIPartnerFizaHUB/docs/FizaHUB-Partner-API-MVP-v1.postman_collection.json'));
    $json = json_decode((string) $raw, true);

    expect($json)->toBeArray()
        ->and($json['info']['schema'] ?? null)
        ->toBe('https://schema.getpostman.com/json/collection/v2.1.0/collection.json')
        ->and($json['item'] ?? [])->toHaveCount(10)
        ->and($raw)->not->toContain('sk_live')
        ->and($raw)->not->toContain('test-fizahub-partner-token');
});

test('postman Extended Beta collection download returns its own filename', function (): void {
    $response = $this->get('/api-fizahub/postman/extended');

    $response->assertOk()
        ->assertDownload('MLHUB-FizaHUB-Partner-API-Extended-Beta.postman_collection.json');

    $raw = file_get_contents(base_path('modules/APIPartnerFizaHUB/docs/FizaHUB-Partner-API-Extended-Beta.postman_collection.json'));
    $json = json_decode((string) $raw, true);

    expect($json)->toBeArray()
        ->and($json['info']['schema'] ?? null)
        ->toBe('https://schema.getpostman.com/json/collection/v2.1.0/collection.json')
        ->and($json['item'] ?? [])->toHaveCount(14)
        ->and($raw)->not->toContain('sk_live')
        ->and($raw)->not->toContain('test-fizahub-partner-token');
});

test('public fizahub help-test page guides postman step by step without secrets', function (): void {
    $response = $this->get('/api-fizahub/help-test');

    $response->assertOk();
    $html = $response->getContent();

    expect($html)->toContain(__('FizaHUB Partner API - Hướng dẫn test Postman từng bước'))
        ->and($html)->toContain(__('Body raw JSON'))
        ->and($html)->toContain('Copy data.request_id')
        ->and($html)->toContain('Copy data.ticket_id')
        ->and($html)->toContain(__('from/to không phải thời hạn gói'))
        ->and($html)->toContain('1/3/6/12')
        ->and($html)->toContain('package_code=base')
        ->and($html)->toContain('mlhub-free-da-nang')
        ->and($html)->toContain('"body"')
        ->and($html)->toContain(__('Lỗi thường gặp'))
        ->and($html)->toContain('Cửa hàng Demo Fiza')
        ->and($html)->toContain('Yêu cầu hỗ trợ FizaMKT Base')
        ->and($html)->toContain('rel="icon"')
        ->and($html)->toContain('fonts.bunny.net')
        ->and($html)->not->toContain('/favicon.ico')
        ->and($html)->not->toContain('test-fizahub-partner-token')
        ->and($html)->not->toContain('FIZAHUB_PARTNER_TOKEN=')
        ->and($html)->not->toContain('sk_live');
});
