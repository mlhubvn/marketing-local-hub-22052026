<?php

function fizahubPostmanCollection(): array
{
    $path = base_path('modules/APIPartnerFizaHUB/docs/FizaHUB-Partner-API.postman_collection.json');
    expect(file_exists($path))->toBeTrue();

    return json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
}

function fizahubPostmanItems(array $folders): array
{
    return collect($folders)
        ->flatMap(fn (array $folder): array => $folder['item'] ?? [])
        ->values()
        ->all();
}

function fizahubPostmanPath(array $item): string
{
    $url = data_get($item, 'request.url', '');
    $raw = is_string($url) ? $url : (string) data_get($item, 'request.url.raw', '');
    $path = str_replace('{{base_url}}/api/v1/partners/fizahub/', '', $raw);

    return explode('?', $path, 2)[0];
}

function fizahubEnvExampleValue(string $key): string
{
    $line = collect(file(base_path('.env.example'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES))
        ->first(fn (string $line): bool => str_starts_with($line, $key.'='));

    expect($line)->toBeString("Missing {$key} in .env.example");

    return explode('=', $line, 2)[1] ?? '';
}

test('postman publishes the exact executable 22 request UI contract', function (): void {
    $collection = fizahubPostmanCollection();
    $folders = $collection['item'] ?? [];

    expect(data_get($collection, 'info.schema'))
        ->toBe('https://schema.getpostman.com/json/collection/v2.1.0/collection.json')
        ->and(collect($folders)->pluck('name')->all())->toBe([
            'System', 'Onboarding', 'Growth', 'Support', 'CRM',
        ])
        ->and(collect($folders)->map(fn (array $folder): int => count($folder['item'] ?? []))->all())
        ->toBe([2, 6, 6, 7, 1]);

    $items = fizahubPostmanItems($folders);
    $actual = collect($items)->map(fn (array $item): string =>
        strtoupper((string) data_get($item, 'request.method')).' '.fizahubPostmanPath($item)
    )->all();

    expect($actual)->toBe([
        'GET health',
        'POST partner/sso/verify',
        'GET marketing-catalog',
        'POST onboarding-requests',
        'GET onboarding-requests/{{onboarding_request_id}}',
        'GET businesses/{{external_business_id}}/marketing-status',
        'PATCH businesses/{{external_business_id}}/profile',
        'PATCH businesses/{{external_business_id}}/marketing-preferences',
        'GET businesses/{{external_business_id}}/dashboard',
        'GET businesses/{{external_business_id}}/growth-insights',
        'GET businesses/{{external_business_id}}/campaigns',
        'GET businesses/{{external_business_id}}/campaigns/{{campaign_id}}',
        'POST businesses/{{external_business_id}}/campaigns/{{campaign_id}}/approval',
        'GET businesses/{{external_business_id}}/package',
        'GET businesses/{{external_business_id}}/support-presets',
        'POST businesses/{{external_business_id}}/support-tickets',
        'GET businesses/{{external_business_id}}/support-tickets',
        'GET businesses/{{external_business_id}}/support-tickets/{{ticket_id}}',
        'POST businesses/{{external_business_id}}/support-tickets/{{ticket_id}}/messages',
        'POST businesses/{{external_business_id}}/support-tickets/{{ticket_id}}/close',
        'POST businesses/{{external_business_id}}/support-tickets/{{ticket_id}}/reopen',
        'POST businesses/{{external_business_id}}/crm-login-links',
    ]);

    $raw = json_encode($collection, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    foreach (['/integration-status', '/packages', '/insights', '/recommendations', '/one-time-login', '/support-summary', '/attachments'] as $legacy) {
        expect($raw)->not->toContain($legacy);
    }
});

test('postman headers variables scripts and dependency guards support a sequential run', function (): void {
    $collection = fizahubPostmanCollection();
    $items = fizahubPostmanItems($collection['item'] ?? []);
    $variables = collect($collection['variable'] ?? [])->pluck('key')->all();

    expect($variables)->toEqualCanonicalizing([
        'base_url', 'partner_token', 'external_user_id', 'external_business_id',
        'onboarding_request_id', 'onboarding_ticket_id', 'ticket_id', 'campaign_id',
        'from', 'to',
    ]);

    foreach ($items as $item) {
        $headers = collect(data_get($item, 'request.header', []))->mapWithKeys(
            fn (array $header): array => [strtolower((string) $header['key']) => (string) $header['value']]
        );
        expect($headers->get('authorization'))->toBe('Bearer {{partner_token}}')
            ->and($headers->get('x-partner'))->toBe('fizahub')
            ->and($headers->get('x-request-id'))->toBe('{{$guid}}')
            ->and($headers->get('accept'))->toBe('application/json');

        $method = strtoupper((string) data_get($item, 'request.method'));
        $path = fizahubPostmanPath($item);
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'], true) && $path !== 'partner/sso/verify') {
            expect($headers->get('idempotency-key'))->toBe('{{$guid}}');
        }

        $requestUrl = data_get($item, 'request.url', '');
        $url = is_string($requestUrl) ? $requestUrl : (string) data_get($item, 'request.url.raw', '');
        if (str_contains($url, '{{onboarding_request_id}}')
            || str_contains($url, '{{campaign_id}}')
            || str_contains($url, '{{ticket_id}}')) {
            $preRequest = collect($item['event'] ?? [])->firstWhere('listen', 'prerequest');
            expect(implode("\n", data_get($preRequest, 'script.exec', [])))->toContain('pm.execution.skipRequest()');
        }
    }

    $onboarding = collect($items)->firstWhere('name', 'Create Onboarding');
    $onboardingTests = implode("\n", data_get(collect($onboarding['event'] ?? [])->firstWhere('listen', 'test'), 'script.exec', []));
    expect($onboardingTests)->toContain('[200, 201, 202]')
        ->and($onboardingTests)->toContain("set('onboarding_request_id'")
        ->and($onboardingTests)->toContain("set('onboarding_ticket_id'");

    $createTicket = collect($items)->firstWhere('name', 'Create Support Ticket');
    $createTicketTests = implode("\n", data_get(collect($createTicket['event'] ?? [])->firstWhere('listen', 'test'), 'script.exec', []));
    expect($createTicketTests)->toContain("set('ticket_id'");

    $listTickets = collect($items)->firstWhere('name', 'List Support Tickets');
    $listTests = implode("\n", data_get(collect($listTickets['event'] ?? [])->firstWhere('listen', 'test'), 'script.exec', []));
    expect($listTests)->toContain("ticket_type !== 'onboarding'")
        ->and($listTests)->not->toContain("set('ticket_id', '{{ticket_id}}')");
});

test('repository Postman defaults to production URL without publishing the partner credential', function (): void {
    $collection = fizahubPostmanCollection();
    $variables = collect($collection['variable'] ?? [])->pluck('value', 'key');

    expect($variables->get('base_url'))->toBe(fizahubEnvExampleValue('APP_URL'))
        ->and($variables->get('partner_token'))->toBe('')
        ->and($variables->get('external_business_id'))->toBe('')
        ->and($variables->get('external_user_id'))->toBe('');

    expect(json_encode($collection))->not->toContain(fizahubEnvExampleValue('FIZAHUB_PARTNER_TOKEN'));

    $preRequest = implode("\n", data_get($collection, 'event.0.script.exec', []));
    expect($preRequest)->toContain("cv.set('external_business_id'")
        ->and($preRequest)->toContain("cv.set('external_user_id'");
});

test('readme and endpoint matrix document the approved cutover without attachment promises', function (): void {
    $readme = (string) file_get_contents(base_path('modules/APIPartnerFizaHUB/README.md'));
    $matrix = (string) file_get_contents(base_path('modules/APIPartnerFizaHUB/docs/ENDPOINT_MATRIX.md'));
    $combined = $readme."\n".$matrix;

    foreach ([
        '22 endpoint', '15 màn hình', 'meta.request_id', 'idempotency_conflict',
        'awaiting_consultant', 'needs_review', 'in_consultation', 'configuring',
        'ready', 'completed', 'cancelled', 'next_cursor', '366',
        'marketing-catalog', 'marketing-preferences', 'growth-insights',
        'support-presets', 'crm-login-links', 'breaking cutover',
    ] as $needle) {
        expect(stripos($combined, $needle))->not->toBeFalse("Missing docs phrase: {$needle}");
    }

    expect(strtolower($combined))->not->toContain('upload support attachment')
        ->and($combined)->not->toContain('/attachments');
});
