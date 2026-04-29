<?php

return [

    'default' => env('FILESYSTEM_DISK', 's3'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
        ],

        's3' => [
            'driver'   => 's3',
            'key'      => env('AWS_ACCESS_KEY_ID'),
            'secret'   => env('AWS_SECRET_ACCESS_KEY'),
            'region'   => env('AWS_DEFAULT_REGION', 'us-east-2'),
            'bucket'   => env('AWS_BUCKET'),
            'url'      => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            /*
             * Disable SSL peer verification when AWS_SSL_VERIFY=false.
             * Set this to false only in local/dev environments where the OS
             * CA bundle is missing or not configured for PHP/cURL (common on
             * Windows). Leave it unset (defaults to true) in production.
             */
            'http' => [
                'verify' => env('AWS_SSL_VERIFY', true),
            ],
        ],

    ],

];
