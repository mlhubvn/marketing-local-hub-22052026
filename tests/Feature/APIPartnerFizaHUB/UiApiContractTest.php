<?php

use Illuminate\Support\Facades\Route;

test('public FizaHUB API is the exact approved 22 route cutover', function (): void {
    $expected = [
        ['GET', 'api/v1/partners/fizahub/health'],
        ['POST', 'api/v1/partners/fizahub/partner/sso/verify'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/marketing-status'],
        ['GET', 'api/v1/partners/fizahub/marketing-catalog'],
        ['POST', 'api/v1/partners/fizahub/onboarding-requests'],
        ['GET', 'api/v1/partners/fizahub/onboarding-requests/{request_id}'],
        ['PATCH', 'api/v1/partners/fizahub/businesses/{external_business_id}/profile'],
        ['PATCH', 'api/v1/partners/fizahub/businesses/{external_business_id}/marketing-preferences'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/dashboard'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/growth-insights'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/campaigns'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/campaigns/{campaign_id}'],
        ['POST', 'api/v1/partners/fizahub/businesses/{external_business_id}/campaigns/{campaign_id}/approval'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/package'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/support-presets'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets'],
        ['POST', 'api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets'],
        ['GET', 'api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}'],
        ['POST', 'api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}/messages'],
        ['POST', 'api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}/close'],
        ['POST', 'api/v1/partners/fizahub/businesses/{external_business_id}/support-tickets/{ticket_id}/reopen'],
        ['POST', 'api/v1/partners/fizahub/businesses/{external_business_id}/crm-login-links'],
    ];

    $actual = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route): bool => str_starts_with($route->uri(), 'api/v1/partners/fizahub'))
        ->map(fn ($route): array => [
            collect($route->methods())->first(fn (string $method): bool => $method !== 'HEAD'),
            $route->uri(),
        ])
        ->values()
        ->all();

    expect($actual)->toEqualCanonicalizing($expected)
        ->and($actual)->toHaveCount(22)
        ->and(collect($actual)->pluck(1))->not->toContain(
            'api/v1/partners/fizahub/support-tickets/{ticket_id}/attachments'
        );
});

test('endpoint matrix publishes exactly the approved 22 public requests', function (): void {
    $matrix = file_get_contents(base_path('modules/APIPartnerFizaHUB/docs/ENDPOINT_MATRIX.md'));

    expect($matrix)->toBeString();

    preg_match_all('/^\|\s*\d+\s*\|\s*(GET|POST|PATCH)\s*\|\s*`(\/api\/v1\/partners\/fizahub[^`]*)`\s*\|/m', $matrix, $matches);

    expect($matches[0])->toHaveCount(22)
        ->and($matrix)->toContain(
            '| 3 | GET | `/api/v1/partners/fizahub/businesses/{external_business_id}/marketing-status` |',
            '| 8 | PATCH | `/api/v1/partners/fizahub/businesses/{external_business_id}/marketing-preferences` |',
            '| 13 | POST | `/api/v1/partners/fizahub/businesses/{external_business_id}/campaigns/{campaign_id}/approval` |',
            '| 22 | POST | `/api/v1/partners/fizahub/businesses/{external_business_id}/crm-login-links` |'
        )
        ->and($matrix)->toContain('Attachment route: removed')
        ->and($matrix)->not->toContain('SupportAttachmentController');
});
