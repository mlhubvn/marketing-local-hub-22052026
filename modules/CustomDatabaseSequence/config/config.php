<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Starting ID for every auto-increment table
    |--------------------------------------------------------------------------
    |
    | The next inserted row in every eligible table will use this number as its
    | primary key, making the platform look "aged" and keeping IDs consistent
    | across modules. Override via the CUSTOM_ID_SEQUENCE_START env key.
    |
    | MySQL behavior: ALTER TABLE ... AUTO_INCREMENT = N sets the next ID to
    | max(N, current_max + 1). So empty tables jump to N, and tables with rows
    | below N also jump to N. Tables already past N are left untouched.
    |
    */
    'starting_id' => (int) env('CUSTOM_ID_SEQUENCE_START', 147123468),

    /*
    |--------------------------------------------------------------------------
    | Auto-run after every migration
    |--------------------------------------------------------------------------
    |
    | When true, the SetIdSequenceCommand is dispatched automatically after
    | `php artisan migrate` and `migrate:fresh` finish, so new tables added
    | by future modules are normalized without any manual step.
    |
    */
    'auto_apply' => env('CUSTOM_ID_SEQUENCE_AUTO_APPLY', true),

    /*
    |--------------------------------------------------------------------------
    | Tables to skip
    |--------------------------------------------------------------------------
    |
    | These tables either use string primary keys, are internal Laravel queues
    | / cache stores, or simply do not benefit from a high starting ID. They
    | are always skipped, even if they happen to expose an AUTO_INCREMENT column.
    |
    */
    'excluded_tables' => [
        'migrations',
        'cache',
        'cache_locks',
        'sessions',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
        'notifications',
        'personal_access_tokens',
    ],
];
