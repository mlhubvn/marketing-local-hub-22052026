<?php

use App\Support\Plans\NoPlanAccess;
use Database\Seeders\AITemplateCategorySeeder;
use Database\Seeders\AITemplateSeeder;
use Database\Seeders\MLHUBBootstrapSeeder;
use Database\Seeders\MLHUBFoundationSeeder;
use Database\Seeders\MLHUBMarketplaceSeeder;
use Database\Seeders\PlanSeeder;
use Modules\CustomMLHUB\Database\Seeders\MLHUBAdminSeeder;
use Modules\CustomMLHUB\Database\Seeders\MLHUBSystemExtrasSeeder;

/**
 * MLHUB bootstrap (không còn Web Installer).
 * Seed + env Coolify thay cho wizard cài đặt lần đầu.
 */
return [
    /** install | update — set bởi mlhub:install / mlhub:update trước db:seed */
    'seeding_mode' => env('MLHUB_SEEDING_MODE', 'install'),

    'locale' => env('APP_LOCALE', 'vi'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Ho_Chi_Minh'),

    'admin_plan_slug' => env('MLHUB_ADMIN_PLAN_SLUG', 'mlhub-partner-lifetime'),

    /*
     * Fallback cho user legacy/manual chưa gán gói (plan_id null): vẫn dùng portal với hạn mức cố định.
     * User đăng ký mới được tự động gán default_signup_plan.
     * Toàn bộ quyền đọc từ env MLHUB_NO_PLAN_* (Coolify) — xem .env.example và ARCHITECTURE_FEATURE.md §1.1.
     */
    'no_plan_access' => [
        'enabled' => filter_var(env('MLHUB_NO_PLAN_ACCESS_ENABLED', true), FILTER_VALIDATE_BOOL),
        'label' => env('MLHUB_NO_PLAN_ACCESS_LABEL', 'Free'),
        'permissions' => NoPlanAccess::permissionsFromEnv(),
    ],

    'license' => [
        // Default thử nghiệm để không phải đặt trên Coolify; đổi purchase_code khi chạy chính thức.
        'purchase_code' => env('MLHUB_LICENSE_PURCHASE_CODE', 'd80177d1-4974-4e46-a7f3-564da3bc83f7'),
        'product_id' => (int) env('MLHUB_LICENSE_PRODUCT_ID', 10252026),
        'version' => env('MLHUB_LICENSE_VERSION', '1.0.1'),
        'install_path' => env('MLHUB_LICENSE_INSTALL_PATH', './'),
        'domain' => env('MLHUB_LICENSE_DOMAIN', 'mlhub.vn'),
        'license_type' => env('MLHUB_LICENSE_TYPE', 'Extended License'),
    ],

    'contact_email' => env('MLHUB_CONTACT_EMAIL', 'admin@mlhub.vn'),

    'site' => [
        'title' => env('SITE_TITLE', 'MKT'),
        'description' => env('SITE_DESCRIPTION', 'Nền tảng Marketing Automation cho hộ kinh doanh tại Việt Nam.'),
        'keywords' => env('SITE_KEYWORDS', 'MKT, marketing, đánh giá Google, đặt lịch, phiếu giảm giá, hộ kinh doanh'),
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
        MLHUBFoundationSeeder::class,
        PlanSeeder::class,
        AITemplateCategorySeeder::class,
        AITemplateSeeder::class,
        MLHUBBootstrapSeeder::class,
        MLHUBMarketplaceSeeder::class,
        MLHUBAdminSeeder::class,
        MLHUBSystemExtrasSeeder::class,
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
