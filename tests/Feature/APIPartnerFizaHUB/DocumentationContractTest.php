<?php

/**
 * Contract tests for the two split Postman collections (Phase 3):
 * - FizaHUB-Partner-API-MVP-v1.postman_collection.json (10 endpoints)
 * - FizaHUB-Partner-API-Extended-Beta.postman_collection.json (14 endpoints)
 */
function decodePostmanCollection(string $filename): array
{
    $path = base_path('modules/APIPartnerFizaHUB/docs/'.$filename);
    expect(file_exists($path))->toBeTrue("missing collection: {$filename}");

    $raw = file_get_contents($path);
    expect($raw)->toBeString()->not->toBeEmpty();

    return [$raw, json_decode($raw, true, 512, JSON_THROW_ON_ERROR)];
}

function assertPostmanCollectionHasNoForbiddenSecrets(string $raw): void
{
    $forbiddenSecrets = [
        'sk_live',
        'sk_test',
        'Bearer live',
        'FIZAHUB_PARTNER_TOKEN=',
        'password123',
        '012345678901',
        'test-fizahub-partner-token',
        'fizahub-partner-token',
    ];

    foreach ($forbiddenSecrets as $needle) {
        expect(stripos($raw, $needle))->toBeFalse("collection must not contain secret fragment: {$needle}");
    }
}

function assertPostmanCollectionHasNoFakeDemoIds(string $raw): void
{
    foreach (['cmp-demo-0001', 'tkt-demo-0001', 'obr-demo', 'onb-demo-0001'] as $needle) {
        expect(stripos($raw, $needle))->toBeFalse("collection must not contain a fake demo id: {$needle}");
    }
}

function assertPostmanItemsHavePartnerHeaders(array $items): void
{
    // POST SSO Verify has no side effects (pure token/session verification), so it is
    // exempt from the Idempotency-Key requirement that applies to mutating POSTs.
    $idempotencyExemptNames = ['1. POST SSO Verify'];

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
        $bodyMode = $item['request']['body']['mode'] ?? null;

        if ($method === 'POST' && ! in_array($item['name'], $idempotencyExemptNames, true)) {
            expect($headers->get('idempotency-key'))->not->toBeNull()
                ->and($headers->get('idempotency-key'))->not->toBe('');
        }

        if (in_array($method, ['POST', 'PATCH'], true) && $bodyMode === 'raw') {
            expect($headers->get('content-type'))->toBe('application/json');
        }

        if ($bodyMode === 'formdata') {
            expect($headers->has('content-type'))->toBeFalse(
                "{$item['name']}: form-data requests must not set Content-Type manually — Postman generates the multipart boundary."
            );
        }
    }
}

test('postman MVP v1 collection is valid v2.1 with exactly ten endpoints, no fake ids, and a safe token placeholder', function (): void {
    [$raw, $collection] = decodePostmanCollection('FizaHUB-Partner-API-MVP-v1.postman_collection.json');

    expect($collection['info']['schema'] ?? null)
        ->toBe('https://schema.getpostman.com/json/collection/v2.1.0/collection.json')
        ->and($collection['info']['name'] ?? null)->toContain('MVP');

    $variables = collect($collection['variable'] ?? [])->pluck('key')->all();
    expect($variables)->toEqualCanonicalizing([
        'base_url',
        'partner_token',
        'external_user_id',
        'external_business_id',
        'onboarding_request_id',
        'ticket_id',
        'from',
        'to',
    ]);

    $variablesByKey = collect($collection['variable'] ?? [])
        ->mapWithKeys(fn (array $variable): array => [
            (string) ($variable['key'] ?? '') => (string) ($variable['value'] ?? ''),
        ]);

    expect($variablesByKey->get('base_url'))->toBe('https://mlhub.vn')
        ->and($variablesByKey->get('partner_token'))->not->toBe('fizahub')
        ->and($variablesByKey->get('partner_token'))->not->toBe('')
        ->and($variablesByKey->get('onboarding_request_id'))->toBe('')
        ->and($variablesByKey->get('ticket_id'))->toBe('');

    $items = $collection['item'] ?? [];
    expect($items)->toHaveCount(10);

    $names = collect($items)->pluck('name')->all();
    expect($names)->toBe([
        '1. GET Health',
        '2. POST Onboarding',
        '3. GET Onboarding Status',
        '4. GET Integration Status',
        '5. GET Business Package',
        '6. GET Dashboard',
        '7. PATCH Update Business Profile',
        '8. POST Create Support Ticket',
        '9. GET Support Ticket Detail',
        '10. POST Upload Support Attachment',
    ]);

    assertPostmanCollectionHasNoForbiddenSecrets($raw);
    assertPostmanCollectionHasNoFakeDemoIds($raw);
    assertPostmanItemsHavePartnerHeaders($items);

    $profileItem = collect($items)->firstWhere('name', '7. PATCH Update Business Profile');
    expect($profileItem['request']['body']['mode'] ?? null)->toBe('raw');

    $profileBody = json_decode((string) ($profileItem['request']['body']['raw'] ?? '{}'), true, 512, JSON_THROW_ON_ERROR);

    expect($profileBody)->toHaveKeys(['owner', 'business'])
        ->and($profileBody)->not->toHaveKey('name')
        ->and($profileBody)->not->toHaveKey('phone')
        ->and($profileBody)->not->toHaveKey('address')
        ->and($profileBody['business'])->toHaveKeys(['name', 'phone', 'address']);

    $attachmentItem = collect($items)->firstWhere('name', '10. POST Upload Support Attachment');
    $attachmentBody = $attachmentItem['request']['body'] ?? [];

    expect($attachmentBody['mode'] ?? null)->toBe('formdata');

    $formdataByKey = collect($attachmentBody['formdata'] ?? [])
        ->mapWithKeys(fn (array $field): array => [(string) ($field['key'] ?? '') => $field]);

    expect($formdataByKey->get('file')['type'] ?? null)->toBe('file')
        ->and($formdataByKey->get('note')['type'] ?? null)->toBe('text');

    $attachmentHeaderKeys = collect($attachmentItem['request']['header'] ?? [])
        ->pluck('key')
        ->map(fn (string $key): string => strtolower($key));

    expect($attachmentHeaderKeys->contains('content-type'))->toBeFalse();

    $onboardingItem = collect($items)->firstWhere('name', '2. POST Onboarding');
    $onboardingScript = implode("\n", $onboardingItem['event'][0]['script']['exec'] ?? []);
    expect($onboardingScript)->toContain("pm.collectionVariables.set('onboarding_request_id'");

    $ticketItem = collect($items)->firstWhere('name', '8. POST Create Support Ticket');
    $ticketScript = implode("\n", $ticketItem['event'][0]['script']['exec'] ?? []);
    expect($ticketScript)->toContain("pm.collectionVariables.set('ticket_id'");
});

test('postman Extended Beta collection is valid v2.1 with exactly fourteen endpoints and no MVP overlap', function (): void {
    [$raw, $collection] = decodePostmanCollection('FizaHUB-Partner-API-Extended-Beta.postman_collection.json');

    expect($collection['info']['schema'] ?? null)
        ->toBe('https://schema.getpostman.com/json/collection/v2.1.0/collection.json')
        ->and($collection['info']['name'] ?? null)->toContain('Extended Beta');

    $variables = collect($collection['variable'] ?? [])->pluck('key')->all();
    expect($variables)->toEqualCanonicalizing([
        'base_url',
        'partner_token',
        'external_business_id',
        'onboarding_request_id',
        'ticket_id',
        'campaign_id',
        'from',
        'to',
    ]);

    $variablesByKey = collect($collection['variable'] ?? [])
        ->mapWithKeys(fn (array $variable): array => [
            (string) ($variable['key'] ?? '') => (string) ($variable['value'] ?? ''),
        ]);

    expect($variablesByKey->get('partner_token'))->not->toBe('fizahub')
        ->and($variablesByKey->get('partner_token'))->not->toBe('')
        ->and($variablesByKey->get('onboarding_request_id'))->toBe('')
        ->and($variablesByKey->get('ticket_id'))->toBe('')
        ->and($variablesByKey->get('campaign_id'))->toBe('');

    $items = $collection['item'] ?? [];
    expect($items)->toHaveCount(14);

    $names = collect($items)->pluck('name')->all();
    expect($names)->toBe([
        '1. POST SSO Verify',
        '2. GET Package Catalog',
        '3. POST Confirm Onboarding',
        '4. POST Cancel Onboarding',
        '5. POST One-time Login',
        '6. GET Insights',
        '7. GET Recommendations',
        '8. GET Campaigns',
        '9. GET Campaign Detail',
        '10. GET Support Summary',
        '11. GET List Support Tickets',
        '12. POST Send Support Message',
        '13. PATCH Close Support Ticket',
        '14. POST Reopen Support Ticket',
    ]);

    $mvpOnlyFragments = [
        'GET Health',
        'POST Onboarding',
        'GET Onboarding Status',
        'GET Integration Status',
        'GET Business Package',
        'GET Dashboard',
        'PATCH Update Business Profile',
        'POST Create Support Ticket',
        'GET Support Ticket Detail',
        'POST Upload Support Attachment',
    ];

    foreach ($mvpOnlyFragments as $mvpFragment) {
        $overlap = collect($names)->contains(fn (string $name): bool => str_contains($name, $mvpFragment));
        expect($overlap)->toBeFalse("Extended Beta collection must not duplicate MVP endpoint: {$mvpFragment}");
    }

    assertPostmanCollectionHasNoForbiddenSecrets($raw);
    assertPostmanCollectionHasNoFakeDemoIds($raw);
    assertPostmanItemsHavePartnerHeaders($items);
});

test('the mvp and extended beta collections together cover exactly the 24 documented core endpoints', function (): void {
    [, $mvp] = decodePostmanCollection('FizaHUB-Partner-API-MVP-v1.postman_collection.json');
    [, $extended] = decodePostmanCollection('FizaHUB-Partner-API-Extended-Beta.postman_collection.json');

    expect(count($mvp['item'] ?? []) + count($extended['item'] ?? []))->toBe(24);
});

test('readme documents partner contracts, onboarding flow, error codes, and the split postman collections', function (): void {
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
        'FizaHUB-Partner-API-MVP-v1.postman_collection.json',
        'FizaHUB-Partner-API-Extended-Beta.postman_collection.json',
        'onboarding_request_not_found',
        'campaign_not_found',
        'ticket_not_found',
        'default_plan_not_found',
        'partner_schema_not_ready',
    ] as $needle) {
        expect(stripos($readme, $needle))->not->toBeFalse("README missing phrase: {$needle}");
    }

    expect(stripos($readme, 'partner_token=fizahub'))->toBeFalse(
        'README must not advertise the weak fizahub token default anymore.'
    );
});
