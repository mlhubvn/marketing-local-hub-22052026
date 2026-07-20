<?php

return [
    'partner_code' => 'fizahub',
    // Default 'fizahub' cho GIAI ĐOẠN THỬ NGHIỆM để đối tác test không cần cấu hình Coolify.
    // ⚠️ Đặt FIZAHUB_PARTNER_TOKEN token mạnh trên Coolify trước khi chạy chính thức.
    'token' => env('FIZAHUB_PARTNER_TOKEN', 'fizahub'),
    'rate_limit_per_minute' => (int) env('FIZAHUB_RATE_LIMIT_PER_MINUTE', 60),
    'one_time_login_ttl_minutes' => (int) env('FIZAHUB_ONE_TIME_LOGIN_TTL_MINUTES', 5),
    'timezone' => 'Asia/Ho_Chi_Minh',
    'default_package' => env('FIZAHUB_DEFAULT_PACKAGE', 'free'),
    'package_map' => [
        'free' => 'mlhub-free-da-nang',
        'base' => 'mlhub-free-da-nang',
    ],
    'marketing_goals' => [
        'local_presence' => [
            'label' => 'Hiện diện',
            'description' => 'Tăng hiện diện địa phương.',
        ],
        'qr_checkin' => [
            'label' => 'QR Check-in',
            'description' => 'Thu lead ngay tại cửa hàng.',
        ],
        'voucher_return' => [
            'label' => 'Mã ưu đãi',
            'description' => 'Khuyến khích khách hàng quay lại.',
        ],
        'customer_retention' => [
            'label' => 'Khách hàng',
            'description' => 'Lưu và chăm sóc khách hàng cũ.',
        ],
    ],
    'provisional_email_domain' => env('FIZAHUB_PROVISIONAL_EMAIL_DOMAIN', 'provisional.mlhub.vn'),
    // URL do FizaHUB cung cấp — để trống thì webhook tự bỏ qua (không lỗi).
    'webhook_base_url' => env('FIZAHUB_WEBHOOK_BASE_URL', ''),
    // Default secret cho giai đoạn thử nghiệm — ⚠️ đổi + chia sẻ lại cho FizaHUB khi chạy chính thức.
    'webhook_secret' => env('FIZAHUB_WEBHOOK_SECRET', '600b3a729836b0461ea51c2067a1911acb83200e0b148404676f037e8556e56a'),
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
