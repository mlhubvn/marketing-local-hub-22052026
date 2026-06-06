<?php

return [
    'starting_id' => (int) env('MLHUB_STARTING_ID', 147123468),

    'first_user' => [
        'email' => env('MLHUB_FIRST_USER_EMAIL', ''),
        'password' => env('MLHUB_FIRST_USER_PASSWORD', ''),
        'name' => env('MLHUB_FIRST_USER_NAME', 'MLHUB Admin'),
        'username' => env('MLHUB_FIRST_USER_USERNAME', 'mlhubadmin'),
        'locale' => env('MLHUB_FIRST_USER_LOCALE', 'vi'),
    ],

    'site_options_file' => __DIR__.'/../Database/data/mlhub_site_options.php',
];
