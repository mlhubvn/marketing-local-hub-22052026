<?php

return [
    'required_php_version' => env('INSTALLER_REQUIRED_PHP_VERSION', '8.3.0'),
    'required_extensions' => [
        'ctype',
        'fileinfo',
        'filter',
        'hash',
        'json',
        'mbstring',
        'openssl',
        'pdo',
        'tokenizer',
        'xml',
    ],
    'writable_paths' => [
        '.env',
        'bootstrap/cache',
        'storage',
    ],
    'purchase_code_required' => env('INSTALLER_PURCHASE_CODE_REQUIRED', true),
    'purchase_verify_url' => 'https://stackposts.com/api/marketplace/install',
    'auto_wipe_database' => env('INSTALLER_AUTO_WIPE_DATABASE', true),
    'final_session_driver' => env('INSTALLER_FINAL_SESSION_DRIVER', 'database'),
    'final_cache_store' => env('INSTALLER_FINAL_CACHE_STORE', 'database'),
    'final_queue_connection' => env('INSTALLER_FINAL_QUEUE_CONNECTION', 'database'),
    'admin_plan_slug' => env('INSTALLER_ADMIN_PLAN_SLUG', 'agency-lifetime'),
    'default_locale' => env('INSTALLER_DEFAULT_LOCALE', 'vi'),
    'default_timezone' => env('INSTALLER_DEFAULT_TIMEZONE', 'Asia/Ho_Chi_Minh'),
    'default_seeders' => [
        \Database\Seeders\MLHUBFoundationSeeder::class,
        \Database\Seeders\PlanSeeder::class,
        \Database\Seeders\AITemplateCategorySeeder::class,
        \Database\Seeders\AITemplateSeeder::class,
    ],
];
