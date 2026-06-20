<?php

use App\Support\BusinessDirectory\BusinessDirectoryQuery;
use Modules\AppBusinessProfiles\Models\LocalBusiness;

test('business directory present maps public activity stats', function () {
    $business = new LocalBusiness([
        'name' => 'Demo Spa',
        'address' => '123 Street',
        'phone' => '0905123456',
        'email' => 'demo@example.com',
        'website' => 'https://example.com',
        'type' => 'spa',
        'industry_group_code' => 'other',
        'industry_category_code' => '',
    ]);
    $business->owner_name = 'Owner Name';
    $business->website = 'https://dacsanquangda.vn';
    $business->google_maps_url = 'https://maps.google.com/?q=Demo';
    $business->address = '78 Cach Mang Thang 8';
    $business->campaigns_count = 5;
    $business->qr_scans_count = 120;
    $business->bookings_count = 18;
    $business->coupon_codes_count = 42;

    $presented = (new ReflectionClass(BusinessDirectoryQuery::class))
        ->getMethod('present')
        ->invoke(new BusinessDirectoryQuery, $business);

    expect($presented['stats'])->toBe([
        'campaigns' => 5,
        'qr_scans' => 120,
        'bookings' => 18,
        'coupon_codes' => 42,
    ])->and($presented['website_url'])->toBe('https://dacsanquangda.vn')
        ->and($presented['website_label'])->toBe('dacsanquangda.vn')
        ->and($presented['google_maps_url'])->toBe('https://maps.google.com/?q=Demo');
});

test('business directory builds google maps directions from address when url missing', function () {
    $business = new LocalBusiness([
        'name' => 'Demo Spa',
        'address' => '112 Vo Nguyen Giap, Da Nang',
    ]);

    $presented = (new ReflectionClass(BusinessDirectoryQuery::class))
        ->getMethod('present')
        ->invoke(new BusinessDirectoryQuery, $business);

    expect($presented['google_maps_url'])->toBe(
        'https://www.google.com/maps/dir/?api=1&destination='.rawurlencode('112 Vo Nguyen Giap, Da Nang')
    );
});
