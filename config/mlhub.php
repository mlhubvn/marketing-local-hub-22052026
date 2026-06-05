<?php

/**
 * MLHUB bootstrap (không còn Web Installer).
 * Seed + env Coolify thay cho wizard cài đặt lần đầu.
 */
return [
    'locale' => 'vi',
    'timezone' => 'Asia/Ho_Chi_Minh',

    'admin_plan_slug' => env('MLHUB_ADMIN_PLAN_SLUG', 'agency-lifetime'),

    /*
     * User đăng ký chưa chọn/gán gói (plan_id null): vẫn dùng portal với hạn mức cố định.
     * Chỉnh trong file này hoặc Coolify env MLHUB_NO_PLAN_ACCESS_* — không cần tạo gói Free trong Admin.
     */
    'no_plan_access' => [
        'enabled' => filter_var(env('MLHUB_NO_PLAN_ACCESS_ENABLED', true), FILTER_VALIDATE_BOOL),
        'label' => env('MLHUB_NO_PLAN_ACCESS_LABEL', 'None'),
        'permissions' => [
            'credits_usage' => true,
            'credits_usage_limit' => (int) env('MLHUB_NO_PLAN_CREDITS_LIMIT', 100),
            'localboost' => true,
            'max_businesses' => (int) env('MLHUB_NO_PLAN_MAX_BUSINESSES', 1),
            'max_campaigns' => (int) env('MLHUB_NO_PLAN_MAX_CAMPAIGNS', 3),
            'max_landing_pages' => (int) env('MLHUB_NO_PLAN_MAX_LANDING_PAGES', 3),
            'max_qr_codes' => (int) env('MLHUB_NO_PLAN_MAX_QR_CODES', 10),
            'max_templates' => (int) env('MLHUB_NO_PLAN_MAX_TEMPLATES', 5),
            'files' => true,
            'max_storage_size_mb' => (int) env('MLHUB_NO_PLAN_MAX_STORAGE_MB', 512),
            'max_file_size_mb' => 32,
            'image_editor' => true,
            'support' => true,
            'ai_studio' => true,
            'ai_studio_caption_generator' => true,
            'ai_studio_content_planner' => false,
            'ai_studio_repurpose' => false,
            'ai_studio_image' => false,
            'advanced_crm' => false,
            'google_business' => false,
            'email_automation' => false,
            'whatsapp_notification' => false,
            'webhook_automation' => false,
            'loyalty_stamp_cards' => false,
            'qr_custom_domains' => false,
        ],
    ],

    /*
     * Admin Faker gắn dữ liệu investor demo (Đà Nẵng SOHO) vào user này.
     * Sau `mlhub:reset-demo` user được tạo bởi LocalBoostDemoSeeder: demo@mlhub.vn / 123456.
     */
    'admin_faker' => [
        'preferred_user_email' => env('MLHUB_ADMIN_FAKER_USER', 'demo@mlhub.vn'),
        'data_file' => 'mlhub_adminfaker_dn_soho.php',
    ],

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
        'guest_theme' => env('THEME_FRONTEND', 'mlhubfrontend'),
        'backend_theme' => env('THEME_BACKEND', 'mlhubbackend'),
        'favicon' => 'img/favicon.svg',
        'logo_dark' => 'img/logo-dark.svg',
        'logo_light' => 'img/logo-light.svg',
        'brand_logo_dark' => 'img/logo-brand-dark.svg',
        'brand_logo_light' => 'img/logo-brand-light.svg',
        'hero_mark' => 'img/mlhub-hero-mark.svg',
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
        'accent_color' => '#ff5f5f',
        'sidebar_bg_color' => '#fffbf8',
        'header_bg_color' => '#ffffff',
        'header_active_color' => '#ff8c42',
        'link_color' => '#ff5f5f',
        'link_hover_color' => '#ff8c42',
        'border_color' => '#ffe5dc',
        'muted_text_color' => '#8a7068',
        'sidebar_text_color' => '#2d1810',
        'header_text_color' => '#2d1810',
        'success_color' => '#059669',
        'warning_color' => '#ffb347',
        'danger_color' => '#dc2626',
        'dark_accent_color' => '#ff8a8a',
        'dark_sidebar_bg_color' => '#1f110c',
        'dark_header_bg_color' => '#251610',
        'dark_header_active_color' => '#ff8c42',
        'dark_link_color' => '#ff7b7b',
        'dark_link_hover_color' => '#ff8c42',
        'dark_border_color' => '#4a3028',
        'dark_muted_text_color' => '#b89a90',
        'dark_sidebar_text_color' => '#ffe5dc',
        'dark_header_text_color' => '#fffbf8',
        'dark_success_color' => '#34d399',
        'dark_warning_color' => '#ffb347',
        'dark_danger_color' => '#f87171',
        'font_family' => 'manrope',
        'layout_width' => 'full',
        'page_max_width' => '90rem',
        'supports_dark_mode' => '1',
        'allow_user_appearance_toggle' => '1',
        'default_appearance' => 'light',
        'density' => 'comfortable',
        'section_spacing' => '1.5rem',
        'preview_mode' => 'desktop',
        'card_radius' => '14',
        'input_radius' => '10',
        'button_radius' => '12',
        'button_style' => 'solid',
        'button_shadow' => 'soft',
    ],

    'guest_theme_settings' => [
        'accent_color' => '#ff5f5f',
        'body_bg_color' => '#fffbf8',
        'surface_bg_color' => '#ffffff',
        'header_bg_color' => '#ffffff',
        'header_text_color' => '#2d1810',
        'link_color' => '#ff5f5f',
        'link_hover_color' => '#ff8c42',
        'border_color' => '#ffe5dc',
        'muted_text_color' => '#8a7068',
        'success_color' => '#059669',
        'warning_color' => '#ffb347',
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
