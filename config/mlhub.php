<?php

/**
 * MLHUB bootstrap (không còn Web Installer).
 * Seed + env Coolify thay cho wizard cài đặt lần đầu.
 */
return [
    'locale' => 'vi',
    'timezone' => 'Asia/Ho_Chi_Minh',

    'admin_plan_slug' => env('MLHUB_ADMIN_PLAN_SLUG', 'agency-lifetime'),

    'license' => [
        'purchase_code' => env('MLHUB_LICENSE_PURCHASE_CODE', 'd80177d1-4974-4e46-a7f3-564da3bc83f7'),
        'product_id' => (int) env('MLHUB_LICENSE_PRODUCT_ID', 10252026),
        'version' => env('MLHUB_LICENSE_VERSION', '1.0.1'),
        'install_path' => env('MLHUB_LICENSE_INSTALL_PATH', './'),
        'domain' => env('MLHUB_LICENSE_DOMAIN', 'mlhub.vn'),
        'license_type' => env('MLHUB_LICENSE_TYPE', 'Extended License'),
    ],

    'contact_email' => env('MLHUB_CONTACT_EMAIL', 'demo@mlhub.vn'),

    'site' => [
        'title' => env('SITE_TITLE', 'MLHUB'),
        'description' => env('SITE_DESCRIPTION', 'Nền tảng Marketing Automation cho hộ kinh doanh tại Việt Nam.'),
        'keywords' => env('SITE_KEYWORDS', 'MLHUB, marketing, đánh giá Google, đặt lịch, phiếu giảm giá, hộ kinh doanh'),
        'guest_theme' => env('THEME_FRONTEND', 'mlhubtheme'),
        'backend_theme' => env('THEME_BACKEND', 'default'),
    ],

    'default_seeders' => [
        \Database\Seeders\MLHUBFoundationSeeder::class,
        \Database\Seeders\PlanSeeder::class,
        \Database\Seeders\AITemplateCategorySeeder::class,
        \Database\Seeders\AITemplateSeeder::class,
        \Database\Seeders\MLHUBBootstrapSeeder::class,
        \Database\Seeders\MLHUBMarketplaceSeeder::class,
        \Database\Seeders\LocalBoostDemoSeeder::class,
    ],

    'backend_theme_settings' => [
        'accent_color' => '#0f766e',
        'sidebar_bg_color' => '#fcfcfd',
        'header_bg_color' => '#fbfcfa',
        'header_active_color' => '#14532d',
        'link_color' => '#0f766e',
        'link_hover_color' => '#115e59',
        'border_color' => '#edf1ed',
        'muted_text_color' => '#667564',
        'sidebar_text_color' => '#3f4b3c',
        'header_text_color' => '#1f2937',
        'success_color' => '#166534',
        'warning_color' => '#ca8a04',
        'danger_color' => '#b91c1c',
        'dark_accent_color' => '#5eead4',
        'dark_sidebar_bg_color' => '#10231c',
        'dark_header_bg_color' => '#13221d',
        'dark_header_active_color' => '#dcfce7',
        'dark_link_color' => '#5eead4',
        'dark_link_hover_color' => '#99f6e4',
        'dark_border_color' => '#35504a',
        'dark_muted_text_color' => '#9fb4aa',
        'dark_sidebar_text_color' => '#d1fae5',
        'dark_header_text_color' => '#f0fdf4',
        'dark_success_color' => '#4ade80',
        'dark_warning_color' => '#facc15',
        'dark_danger_color' => '#f87171',
    ],

    'guest_theme_settings' => [
        'accent_color' => '#ff5f5f',
        'body_bg_color' => '#fffbf8',
        'surface_bg_color' => '#ffffff',
        'header_bg_color' => '#ffffff',
        'header_text_color' => '#2d1810',
        'link_color' => '#ff5f5f',
        'link_hover_color' => '#e84a3a',
        'border_color' => '#ffe5dc',
        'muted_text_color' => '#8a7068',
        'success_color' => '#059669',
        'warning_color' => '#ff8c42',
        'danger_color' => '#dc2626',
        'font_family' => 'manrope',
        'page_max_width' => '86rem',
        'default_appearance' => 'light',
        'supports_dark_mode' => '1',
        'allow_user_appearance_toggle' => '1',
        'section_spacing' => '5rem',
        'card_radius' => '18',
        'input_radius' => '14',
        'button_radius' => '14',
    ],
];
