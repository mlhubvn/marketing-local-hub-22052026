<?php

use Illuminate\Http\Request;

function bindRequestHost(string $url): void
{
    app()->instance('request', Request::create($url, 'GET'));
}

test('site_brand_title keeps configured fallback on the primary APP_URL host', function (): void {
    config(['app.url' => 'https://mlhub.vn']);
    bindRequestHost('https://mlhub.vn/login');

    expect(site_brand_title('MLHUB.vn'))->toBe('MLHUB.vn');
});

test('site_brand_title uses the request host on white-label domains', function (): void {
    config(['app.url' => 'https://mlhub.vn']);
    bindRequestHost('https://fzh.vmo.com.vn/login');

    expect(site_brand_title('MLHUB.vn'))->toBe('fzh.vmo.com.vn');
});

test('site_brand_title treats www primary host as the same site', function (): void {
    config(['app.url' => 'https://www.mlhub.vn']);
    bindRequestHost('https://mlhub.vn/login');

    expect(site_brand_title('MKT'))->toBe('MKT');
});
