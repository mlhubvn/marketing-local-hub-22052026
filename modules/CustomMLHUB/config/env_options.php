<?php

/**
 * Map Coolify / .env → Admin Settings (bảng options).
 *
 * Thứ tự ưu tiên khi mlhub:sync-env-options chạy (docker/entrypoint.sh mỗi deploy):
 *   1. env (Coolify) có giá trị thật  → luôn ghi đè option.
 *   2. env trống nhưng có 'default'    → CHỈ điền khi option còn trống (không đè admin đã chỉnh).
 *   3. không có gì                     → bỏ qua.
 *
 * 'default' = giá trị mặc định trong code cho GIAI ĐOẠN THỬ NGHIỆM, giúp giảm số biến phải đặt
 * trên Coolify. ⚠️ Đổi các secret (mail/oauth/captcha) trước khi chạy chính thức.
 *
 * @return list<array{env: string, option: string, default?: string, transform?: callable(mixed): mixed}>
 */
return [
  // Site & locale
    ['env' => 'SITE_TITLE', 'option' => 'website_title', 'default' => 'MKT'],
    ['env' => 'SITE_TITLE', 'option' => 'contact_company_name', 'default' => 'MKT'],
    ['env' => 'SITE_DESCRIPTION', 'option' => 'website_description', 'default' => 'Nền tảng Marketing Automation hỗ trợ tăng đánh giá, đặt lịch, mã ưu đãi, phản hồi & tạo khách hàng tiềm năng.'],
    ['env' => 'SITE_KEYWORDS', 'option' => 'website_keyword', 'default' => 'MKT, Marketing Automation, đánh giá, đặt lịch, mã ưu đãi, phản hồi, khách hàng tiềm năng, hộ kinh doanh'],
    ['env' => 'THEME_FRONTEND', 'option' => 'frontend_theme', 'default' => 'mlhubfrontend'],
    ['env' => 'THEME_BACKEND', 'option' => 'backend_theme', 'default' => 'mlhubbackend'],
    ['env' => 'MLHUB_CONTACT_EMAIL', 'option' => 'contact_email', 'default' => 'admin@mlhub.vn'],
    ['env' => 'MLHUB_CONTACT_PHONE', 'option' => 'contact_phone_number', 'default' => '0899789225'],
    ['env' => 'MLHUB_CONTACT_WEBSITE', 'option' => 'contact_company_website', 'default' => 'https://mlhub.vn'],
    ['env' => 'MLHUB_CONTACT_LOCATION', 'option' => 'contact_location', 'default' => '01 Nguyễn Văn Linh, Đà Nẵng'],
    ['env' => 'MLHUB_CONTACT_WORKING_HOURS', 'option' => 'contact_working_hours', 'default' => 'Thứ 2 - Thứ 6: 09:00 - 18:00'],
    ['env' => 'APP_TIMEZONE', 'option' => 'app_timezone', 'default' => 'Asia/Ho_Chi_Minh'],
    ['env' => 'APP_LOCALE', 'option' => 'default_locale', 'default' => 'vi'],

  // Mail (Admin → Mail Server) — ⚠️ secret_* là API key thử nghiệm, đổi khi chạy chính thức
    ['env' => 'MAIL_MAILER', 'option' => 'mail_protocol', 'default' => 'smtp'],
    ['env' => 'MAIL_HOST', 'option' => 'smtp_server', 'default' => 'smtp.emailit.com'],
    ['env' => 'MAIL_PORT', 'option' => 'smtp_port', 'default' => '587', 'transform' => static fn ($v) => (string) $v],
    ['env' => 'MAIL_USERNAME', 'option' => 'smtp_username', 'default' => 'emailit'],
    ['env' => 'MAIL_PASSWORD', 'option' => 'smtp_password', 'default' => 'secret_LHzlnBLwXz8ZGV0JztVkvn9mf4VFKE14'],
    ['env' => 'MAIL_FROM_ADDRESS', 'option' => 'mail_sender_email', 'default' => 'noreply@mlhub.vn'],
    ['env' => 'MAIL_FROM_NAME', 'option' => 'mail_sender_name', 'default' => 'MKT'],
    ['env' => 'MAIL_SCHEME', 'option' => 'smtp_encryption', 'default' => 'tls', 'transform' => static fn ($v) => match (strtolower((string) $v)) {
        'ssl' => 'ssl',
        'tls' => 'tls',
        default => 'none',
    }],
    ['env' => 'MAIL_EHLO_DOMAIN', 'option' => 'mail_ehlo_domain', 'default' => 'mlhub.vn'],

  // Captcha (Admin → Captcha) — ⚠️ secret key thử nghiệm, đổi khi chạy chính thức
    ['env' => 'MLHUB_CAPTCHA_TYPE', 'option' => 'captcha_type', 'default' => 'turnstile'],
    ['env' => 'MLHUB_CLOUDFLARE_TURNSTILE_STATUS', 'option' => 'auth_cloudflare_turnstile_status', 'default' => '1', 'transform' => 'bool01'],
    ['env' => 'MLHUB_CLOUDFLARE_TURNSTILE_SITE_KEY', 'option' => 'auth_cloudflare_turnstile_site_key', 'default' => '0x4AAAAAADYVJ5pkiNy7poMX'],
    ['env' => 'MLHUB_CLOUDFLARE_TURNSTILE_SECRET_KEY', 'option' => 'auth_cloudflare_turnstile_secret_key', 'default' => '0x4AAAAAADYVJw4b7xm2WC3UNugtP1miHzw'],
    ['env' => 'MLHUB_GOOGLE_RECAPTCHA_STATUS', 'option' => 'auth_google_recaptcha_status', 'default' => '0', 'transform' => 'bool01'],
    ['env' => 'MLHUB_GOOGLE_RECAPTCHA_SITE_KEY', 'option' => 'auth_google_recaptcha_site_key', 'default' => '6LeeAAItAAAAABtl-viskX16U6_QbyjCXrz2v6HG'],
    ['env' => 'MLHUB_GOOGLE_RECAPTCHA_SECRET_KEY', 'option' => 'auth_google_recaptcha_secret_key', 'default' => '6LeeAAItAAAAAM6gBf1GGNsO6wSA8OteZTG3ZHXV'],

  // Authentication (Admin → Authentication Rules — /admin/settings/auth)
    ['env' => 'MLHUB_AUTH_LANDING_PAGE_STATUS', 'option' => 'auth_landing_page_status', 'default' => '1', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_SIGNUP_PAGE_STATUS', 'option' => 'auth_signup_page_status', 'default' => '1', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_ACTIVATION_EMAIL_STATUS', 'option' => 'auth_activation_email_new_user_status', 'default' => '1', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_WELCOME_EMAIL_STATUS', 'option' => 'auth_welcome_email_new_user_status', 'default' => '1', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_USER_CHANGE_EMAIL_STATUS', 'option' => 'auth_user_change_email_status', 'default' => '0', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_USER_CHANGE_USERNAME_STATUS', 'option' => 'auth_user_change_username_status', 'default' => '0', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_TWO_FACTOR_STATUS', 'option' => 'auth_two_factor_authentication_status', 'default' => '1', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_GOOGLE_LOGIN_STATUS', 'option' => 'auth_google_login_status', 'default' => '1', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_GOOGLE_LOGIN_CLIENT_ID', 'option' => 'auth_google_login_client_id', 'default' => '577193085095-npcc1ilqv20dekqe08orcm3e6htao7tq.apps.googleusercontent.com'],
    ['env' => 'MLHUB_AUTH_GOOGLE_LOGIN_CLIENT_SECRET', 'option' => 'auth_google_login_client_secret', 'default' => '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'],
    ['env' => 'MLHUB_AUTH_FACEBOOK_LOGIN_STATUS', 'option' => 'auth_facebook_login_status', 'default' => '0', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_FACEBOOK_LOGIN_APP_ID', 'option' => 'auth_facebook_login_app_id'],
    ['env' => 'MLHUB_AUTH_FACEBOOK_LOGIN_APP_SECRET', 'option' => 'auth_facebook_login_app_secret'],
    ['env' => 'MLHUB_AUTH_FACEBOOK_LOGIN_APP_VERSION', 'option' => 'auth_facebook_login_app_version', 'default' => 'v22.0'],
    ['env' => 'MLHUB_AUTH_X_LOGIN_STATUS', 'option' => 'auth_x_login_status', 'default' => '0', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_X_LOGIN_CLIENT_ID', 'option' => 'auth_x_login_client_id'],
    ['env' => 'MLHUB_AUTH_X_LOGIN_CLIENT_SECRET', 'option' => 'auth_x_login_client_secret'],

  // Google Analytics (Admin → Analytics)
    ['env' => 'MLHUB_GOOGLE_ANALYTICS_STATUS', 'option' => 'google_analytics_status', 'default' => '1', 'transform' => 'bool01'],
    ['env' => 'MLHUB_GOOGLE_ANALYTICS_MEASUREMENT_ID', 'option' => 'google_analytics_measurement_id', 'default' => 'G-ZY1P6YJLME'],
    ['env' => 'MLHUB_GOOGLE_ANALYTICS_TRACK_GUEST', 'option' => 'google_analytics_track_guest', 'default' => '1', 'transform' => 'bool01'],
    ['env' => 'MLHUB_GOOGLE_ANALYTICS_TRACK_APP', 'option' => 'google_analytics_track_app', 'default' => '1', 'transform' => 'bool01'],

  // Google Business OAuth (Portal integration + Admin → API Integration)
    ['env' => 'MLHUB_GOOGLE_BUSINESS_STATUS', 'option' => 'integration_google_business_profile_status', 'default' => '1', 'transform' => 'bool01'],
    ['env' => 'GOOGLE_BUSINESS_CLIENT_ID', 'option' => 'integration_google_business_profile_client_id', 'default' => '577193085095-v34ha4i8peqqe3fo2gsd3crs2dldooct.apps.googleusercontent.com'],
    ['env' => 'GOOGLE_BUSINESS_CLIENT_SECRET', 'option' => 'integration_google_business_profile_client_secret', 'default' => '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'],

  // Stripe (Admin → Payment gateways) — tắt mặc định, nhập key trong Admin khi cần
    ['env' => 'STRIPE_STATUS', 'option' => 'stripe_status', 'default' => '0', 'transform' => 'bool01'],
    ['env' => 'STRIPE_PUBLISHABLE_KEY', 'option' => 'stripe_publishable_key'],
    ['env' => 'STRIPE_SECRET_KEY', 'option' => 'stripe_secret_key'],
    ['env' => 'STRIPE_WEBHOOK_SECRET', 'option' => 'stripe_webhook_secret'],

  // License (Admin → License)
    ['env' => 'MLHUB_LICENSE_PURCHASE_CODE', 'option' => 'license_purchase_code', 'default' => 'd80177d1-4974-4e46-a7f3-564da3bc83f7'],
    ['env' => 'MLHUB_LICENSE_PRODUCT_ID', 'option' => 'license_product_id', 'default' => '10252026', 'transform' => static fn ($v) => (string) $v],
    ['env' => 'MLHUB_LICENSE_VERSION', 'option' => 'license_version', 'default' => '1.0.1'],
    ['env' => 'MLHUB_LICENSE_INSTALL_PATH', 'option' => 'license_install_path', 'default' => './'],

  // Cron secure key (Admin → Crons)
    ['env' => 'MLHUB_SYSTEM_CRON_SECURE_KEY', 'option' => 'system_cron_secure_key'],
];
