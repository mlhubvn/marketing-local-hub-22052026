<?php

test('postman collection is valid v2.1 with exactly twenty four core api requests', function (): void {
    $path = base_path('modules/APIPartnerFizaHUB/docs/FizaHUB-Partner-API.postman_collection.json');
    expect(file_exists($path))->toBeTrue();

    $raw = file_get_contents($path);
    expect($raw)->toBeString()->not->toBeEmpty();

    $collection = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

    expect($collection['info']['schema'] ?? null)
        ->toBe('https://schema.getpostman.com/json/collection/v2.1.0/collection.json');

    $variables = collect($collection['variable'] ?? [])->pluck('key')->all();
    expect($variables)->toEqualCanonicalizing([
        'base_url',
        'partner_token',
        'external_user_id',
        'external_business_id',
        'onboarding_request_id',
        'ticket_id',
        'campaign_id',
        'from',
        'to',
    ]);

    $items = $collection['item'] ?? [];
    expect($items)->toHaveCount(24);

    $names = collect($items)->pluck('name')->all();
    expect($names)->toBe([
        'GET Health',
        'POST SSO Verify',
        'GET Package Catalog',
        'POST Onboarding',
        'GET Onboarding Status',
        'POST Confirm Onboarding',
        'POST Cancel Onboarding',
        'GET Integration Status',
        'PATCH Update Business Profile',
        'POST One-time Login',
        'GET Business Package',
        'GET Dashboard',
        'GET Insights',
        'GET Recommendations',
        'GET Campaigns',
        'GET Campaign Detail',
        'GET Support Summary',
        'POST Create Support Ticket',
        'GET List Support Tickets',
        'GET Support Ticket Detail',
        'POST Send Support Message',
        'POST Upload Support Attachment',
        'PATCH Close Support Ticket',
        'POST Reopen Support Ticket',
    ]);

    $forbiddenSecrets = [
        'sk_live',
        'sk_test',
        'Bearer live',
        'FIZAHUB_PARTNER_TOKEN=',
        'password123',
        '012345678901',
        'test-fizahub-partner-token',
    ];

    foreach ($forbiddenSecrets as $needle) {
        expect(stripos($raw, $needle))->toBeFalse("collection must not contain secret fragment: {$needle}");
    }

    $variablesByKey = collect($collection['variable'] ?? [])
        ->mapWithKeys(fn (array $variable): array => [
            (string) ($variable['key'] ?? '') => (string) ($variable['value'] ?? ''),
        ]);

    expect($variablesByKey->get('base_url'))->toBe('https://mlhub.vn')
        ->and($variablesByKey->get('partner_token'))->toBe('fizahub')
        ->and($variablesByKey->get('external_business_id'))->toBe('fh-biz-demo-001')
        ->and($raw)->toContain('package_code')
        ->and($raw)->toContain('requested_package_code')
        ->and($raw)->toContain('nguyenvana+demo001@example.com')
        ->and($raw)->toContain('Need help with FizaMKT Base')
        ->and($raw)->toContain('Fiza Demo Store - Com Tam Da Nang')
        ->and($raw)->toContain('Thành công')
        ->and($raw)->toContain('Lỗi người dùng')
        ->and($raw)->toContain('Lỗi nghiệp vụ')
        ->and($raw)->toContain('Lỗi kỹ thuật')
        ->and($raw)->toContain('validation_failed')
        ->and($raw)->toContain('integration_not_found')
        ->and($raw)->toContain('ticket_not_open')
        ->and($raw)->toContain('onboarding_not_ready')
        ->and($raw)->toContain('partner_api_error')
        ->and($raw)->toContain('awaiting_consultant')
        ->and($raw)->toContain('needs_review')
        ->and($raw)->toContain('ready')
        ->and($raw)->toContain('completed');

    // Testing phase: partner_token is pre-filled with the weak default 'fizahub' so partners can
    // download and test immediately. It must never carry a strong/real production secret here.
    expect($variablesByKey->get('partner_token'))
        ->toBe('fizahub')
        ->and($variablesByKey->get('partner_token'))->not->toBe('');

    $exampleCount = collect($items)->sum(fn (array $item): int => count($item['response'] ?? []));
    expect($exampleCount)->toBeGreaterThanOrEqual(40);

    $idempotentPosts = [
        'POST Onboarding',
        'POST Create Support Ticket',
        'POST Send Support Message',
    ];

    foreach ($items as $item) {
        $headers = collect($item['request']['header'] ?? [])
            ->mapWithKeys(fn (array $header): array => [
                strtolower((string) ($header['key'] ?? '')) => (string) ($header['value'] ?? ''),
            ]);

        expect($headers->get('authorization'))->toBe('Bearer {{partner_token}}')
            ->and($headers->get('x-partner'))->toBe('fizahub')
            ->and($headers->get('x-request-id'))->toBe('{{$guid}}')
            ->and($headers->get('accept'))->toBe('application/json');

        $method = strtoupper((string) ($item['request']['method'] ?? ''));
        $hasBody = isset($item['request']['body']);

        if ($method === 'POST' || $hasBody) {
            expect($headers->get('content-type'))->toBe('application/json');
        }

        if (in_array($item['name'], $idempotentPosts, true)) {
            expect($headers->get('idempotency-key'))->not->toBeNull()
                ->and($headers->get('idempotency-key'))->not->toBe('');
        }

        $url = $item['request']['url'] ?? '';
        $urlRaw = is_array($url) ? (string) ($url['raw'] ?? '') : (string) $url;

        if ($item['name'] === 'GET Support Ticket Detail' || $item['name'] === 'POST Send Support Message') {
            expect($urlRaw)->toContain('external_business_id={{external_business_id}}');
        }
    }
});

test('readme documents partner contracts and onboarding flow', function (): void {
    $readme = file_get_contents(base_path('modules/APIPartnerFizaHUB/README.md'));
    expect($readme)->toBeString();

    foreach ([
        '24 Core API',
        'FizaHUB gửi yêu cầu',
        'MLHUB tạo ngay tài khoản Free',
        'Chờ tư vấn viên',
        'awaiting_consultant',
        'needs_review',
        'ready',
        'completed',
        'Chờ tư vấn viên liên hệ',
        'Cần kiểm tra',
        'Sẵn sàng sử dụng',
        'Hoàn tất',
        'one-time login',
        'onboarding_not_ready',
        'Limited dashboard metrics',
        'no CCCD upload',
        '/api/v1/partners/fizahub',
        'external_business_id',
        'Idempotency-Key',
        'identity_card',
        'identity_image',
        'business_license_image',
        'field **`body`**',
        'partner_token=fizahub',
    ] as $needle) {
        expect(stripos($readme, $needle))->not->toBeFalse("README missing phrase: {$needle}");
    }
});
