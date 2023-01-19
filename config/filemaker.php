<?php

return [
    'default'  => [
        'hostname' => env('FM_HOSTNAME'),
        'port'     => env('FM_PORT', null),
        'protocol' => env('FM_PROTOCOL', 'https://'),

        'version'  => env('FM_VERSION', 'v1'), // v1 or vLatest
        'database' => env('FM_DATABASE', 'default'),

        'username' => env('FM_USERNAME'),
        'password' => env('FM_PASSWORD'),
    ],
    'settings' => [
        'boolean_true_values' => ['true', '1', 'yes', 'y', 'j', 'ja'],
    ],
];