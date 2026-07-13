<?php

return [
    'partner_code' => 'fizahub',
    'token' => env('FIZAHUB_PARTNER_TOKEN', ''),
    'rate_limit_per_minute' => (int) env('FIZAHUB_RATE_LIMIT_PER_MINUTE', 60),
    'one_time_login_ttl_minutes' => (int) env('FIZAHUB_ONE_TIME_LOGIN_TTL_MINUTES', 5),
    'timezone' => 'Asia/Ho_Chi_Minh',
    'default_package' => 'base',
    'package_map' => [
        'base' => 'mlhub-free-da-nang',
    ],
    'industry_aliases' => [
        'restaurant_food' => 'restaurant_eatery',
    ],
];
