<?php

return [
    'partner_code' => 'fizahub',
    'token' => env('FIZAHUB_PARTNER_TOKEN', ''),
    'rate_limit_per_minute' => (int) env('FIZAHUB_RATE_LIMIT_PER_MINUTE', 60),
    'one_time_login_ttl_minutes' => (int) env('FIZAHUB_ONE_TIME_LOGIN_TTL_MINUTES', 5),
    'timezone' => 'Asia/Ho_Chi_Minh',
    'default_package' => env('FIZAHUB_DEFAULT_PACKAGE', 'free'),
    'package_map' => [
        'free' => 'mlhub-free-da-nang',
        'base' => 'mlhub-free-da-nang',
    ],
    'provisional_email_domain' => env('FIZAHUB_PROVISIONAL_EMAIL_DOMAIN', 'provisional.fizahub.mlhub.local'),
    'webhook_base_url' => env('FIZAHUB_WEBHOOK_BASE_URL', ''),
    'webhook_secret' => env('FIZAHUB_WEBHOOK_SECRET', ''),
    'dashboard_cache_ttl_minutes' => (int) env('FIZAHUB_DASHBOARD_CACHE_TTL_MINUTES', 45),
    'dashboard_force_refresh_cooldown_seconds' => (int) env('FIZAHUB_DASHBOARD_FORCE_REFRESH_COOLDOWN_SECONDS', 300),
    'support_max_attachment_size_mb' => (int) env('FIZAHUB_SUPPORT_MAX_ATTACHMENT_SIZE_MB', 10),
    'support_allowed_attachment_types' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'application/pdf',
        'text/plain',
    ],
    'support_categories' => [
        ['code' => 'general', 'label' => 'Hỗ trợ chung'],
        ['code' => 'campaign', 'label' => 'Chiến dịch & QR'],
        ['code' => 'account', 'label' => 'Tài khoản & gói dịch vụ'],
        ['code' => 'billing', 'label' => 'Thanh toán'],
        ['code' => 'technical', 'label' => 'Kỹ thuật'],
    ],
    'industry_aliases' => [
        'restaurant_food' => 'restaurant_eatery',
    ],
];
