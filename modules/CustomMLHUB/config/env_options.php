<?php

/**
 * Map Coolify / .env → Admin Settings (bảng options).
 * Chỉ ghi khi env có giá trị thật (không placeholder .env.example).
 *
 * @return list<array{env: string, option: string, transform?: callable(mixed): mixed}>
 */
return [
  // Site & locale
    ['env' => 'SITE_TITLE', 'option' => 'website_title'],
    ['env' => 'SITE_TITLE', 'option' => 'contact_company_name'],
    ['env' => 'SITE_DESCRIPTION', 'option' => 'website_description'],
    ['env' => 'SITE_KEYWORDS', 'option' => 'website_keyword'],
    ['env' => 'THEME_FRONTEND', 'option' => 'frontend_theme'],
    ['env' => 'THEME_BACKEND', 'option' => 'backend_theme'],
    ['env' => 'MLHUB_CONTACT_EMAIL', 'option' => 'contact_email'],
    ['env' => 'MLHUB_CONTACT_PHONE', 'option' => 'contact_phone_number'],
    ['env' => 'MLHUB_CONTACT_WEBSITE', 'option' => 'contact_company_website'],
    ['env' => 'MLHUB_CONTACT_LOCATION', 'option' => 'contact_location'],
    ['env' => 'MLHUB_CONTACT_WORKING_HOURS', 'option' => 'contact_working_hours'],
    ['env' => 'APP_TIMEZONE', 'option' => 'app_timezone'],
    ['env' => 'APP_LOCALE', 'option' => 'default_locale'],

  // Mail (Admin → Mail Server)
    ['env' => 'MAIL_MAILER', 'option' => 'mail_protocol'],
    ['env' => 'MAIL_HOST', 'option' => 'smtp_server'],
    ['env' => 'MAIL_PORT', 'option' => 'smtp_port', 'transform' => static fn ($v) => (string) $v],
    ['env' => 'MAIL_USERNAME', 'option' => 'smtp_username'],
    ['env' => 'MAIL_PASSWORD', 'option' => 'smtp_password'],
    ['env' => 'MAIL_FROM_ADDRESS', 'option' => 'mail_sender_email'],
    ['env' => 'MAIL_FROM_NAME', 'option' => 'mail_sender_name'],
    ['env' => 'MAIL_SCHEME', 'option' => 'smtp_encryption', 'transform' => static fn ($v) => match (strtolower((string) $v)) {
        'ssl' => 'ssl',
        'tls' => 'tls',
        default => 'none',
    }],
    ['env' => 'MAIL_EHLO_DOMAIN', 'option' => 'mail_ehlo_domain'],

  // Captcha (Admin → Captcha)
    ['env' => 'MLHUB_CAPTCHA_TYPE', 'option' => 'captcha_type'],
    ['env' => 'MLHUB_CLOUDFLARE_TURNSTILE_STATUS', 'option' => 'auth_cloudflare_turnstile_status', 'transform' => 'bool01'],
    ['env' => 'MLHUB_CLOUDFLARE_TURNSTILE_SITE_KEY', 'option' => 'auth_cloudflare_turnstile_site_key'],
    ['env' => 'MLHUB_CLOUDFLARE_TURNSTILE_SECRET_KEY', 'option' => 'auth_cloudflare_turnstile_secret_key'],
    ['env' => 'MLHUB_GOOGLE_RECAPTCHA_STATUS', 'option' => 'auth_google_recaptcha_status', 'transform' => 'bool01'],
    ['env' => 'MLHUB_GOOGLE_RECAPTCHA_SITE_KEY', 'option' => 'auth_google_recaptcha_site_key'],
    ['env' => 'MLHUB_GOOGLE_RECAPTCHA_SECRET_KEY', 'option' => 'auth_google_recaptcha_secret_key'],

  // Authentication (Admin → Authentication Rules — /admin/settings/auth)
    ['env' => 'MLHUB_AUTH_LANDING_PAGE_STATUS', 'option' => 'auth_landing_page_status', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_SIGNUP_PAGE_STATUS', 'option' => 'auth_signup_page_status', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_ACTIVATION_EMAIL_STATUS', 'option' => 'auth_activation_email_new_user_status', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_WELCOME_EMAIL_STATUS', 'option' => 'auth_welcome_email_new_user_status', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_USER_CHANGE_EMAIL_STATUS', 'option' => 'auth_user_change_email_status', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_USER_CHANGE_USERNAME_STATUS', 'option' => 'auth_user_change_username_status', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_TWO_FACTOR_STATUS', 'option' => 'auth_two_factor_authentication_status', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_GOOGLE_LOGIN_STATUS', 'option' => 'auth_google_login_status', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_GOOGLE_LOGIN_CLIENT_ID', 'option' => 'auth_google_login_client_id'],
    ['env' => 'MLHUB_AUTH_GOOGLE_LOGIN_CLIENT_SECRET', 'option' => 'auth_google_login_client_secret'],
    ['env' => 'MLHUB_AUTH_FACEBOOK_LOGIN_STATUS', 'option' => 'auth_facebook_login_status', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_FACEBOOK_LOGIN_APP_ID', 'option' => 'auth_facebook_login_app_id'],
    ['env' => 'MLHUB_AUTH_FACEBOOK_LOGIN_APP_SECRET', 'option' => 'auth_facebook_login_app_secret'],
    ['env' => 'MLHUB_AUTH_FACEBOOK_LOGIN_APP_VERSION', 'option' => 'auth_facebook_login_app_version'],
    ['env' => 'MLHUB_AUTH_X_LOGIN_STATUS', 'option' => 'auth_x_login_status', 'transform' => 'bool01'],
    ['env' => 'MLHUB_AUTH_X_LOGIN_CLIENT_ID', 'option' => 'auth_x_login_client_id'],
    ['env' => 'MLHUB_AUTH_X_LOGIN_CLIENT_SECRET', 'option' => 'auth_x_login_client_secret'],

  // Google Analytics (Admin → Analytics)
    ['env' => 'MLHUB_GOOGLE_ANALYTICS_STATUS', 'option' => 'google_analytics_status', 'transform' => 'bool01'],
    ['env' => 'MLHUB_GOOGLE_ANALYTICS_MEASUREMENT_ID', 'option' => 'google_analytics_measurement_id'],
    ['env' => 'MLHUB_GOOGLE_ANALYTICS_TRACK_GUEST', 'option' => 'google_analytics_track_guest', 'transform' => 'bool01'],
    ['env' => 'MLHUB_GOOGLE_ANALYTICS_TRACK_APP', 'option' => 'google_analytics_track_app', 'transform' => 'bool01'],

  // Google Business OAuth (Portal integration + Admin → API Integration)
    ['env' => 'MLHUB_GOOGLE_BUSINESS_STATUS', 'option' => 'integration_google_business_profile_status', 'transform' => 'bool01'],
    ['env' => 'GOOGLE_BUSINESS_CLIENT_ID', 'option' => 'integration_google_business_profile_client_id'],
    ['env' => 'GOOGLE_BUSINESS_CLIENT_SECRET', 'option' => 'integration_google_business_profile_client_secret'],

  // Stripe (Admin → Payment gateways)
    ['env' => 'STRIPE_STATUS', 'option' => 'stripe_status', 'transform' => 'bool01'],
    ['env' => 'STRIPE_PUBLISHABLE_KEY', 'option' => 'stripe_publishable_key'],
    ['env' => 'STRIPE_SECRET_KEY', 'option' => 'stripe_secret_key'],
    ['env' => 'STRIPE_WEBHOOK_SECRET', 'option' => 'stripe_webhook_secret'],

  // License (Admin → License)
    ['env' => 'MLHUB_LICENSE_PURCHASE_CODE', 'option' => 'license_purchase_code'],
    ['env' => 'MLHUB_LICENSE_PRODUCT_ID', 'option' => 'license_product_id', 'transform' => static fn ($v) => (string) $v],
    ['env' => 'MLHUB_LICENSE_VERSION', 'option' => 'license_version'],
    ['env' => 'MLHUB_LICENSE_INSTALL_PATH', 'option' => 'license_install_path'],

  // Cron secure key (Admin → Crons)
    ['env' => 'MLHUB_SYSTEM_CRON_SECURE_KEY', 'option' => 'system_cron_secure_key'],
];
