<?php

return [
    'default' => 'ms-keys',

    'ms-keys' => [
        'connections' => [
            'sync' => [
                'driver' => 'sync',
            ],
            'database' => [
                'driver' => 'database',
                'table' => 'jobs',
                'queue' => 'default',
                'retry_after' => 90,
                'after_commit' => false,
            ],

            'redis' => [
                'driver' => 'redis',
                'connection' => 'default',
                'queue' => env('REDIS_QUEUE', 'default'),
                'retry_after' => 90,
                'block_for' => null,
                'after_commit' => false,
            ],

            'sqs' => [
                'driver' => 'sqs',
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
                'prefix' => env('SQS_PREFIX', ''),
                'queue' => env('SQS_QUEUE', 'https://sqs.sa-east-1.amazonaws.com/448869868355/TesteQueue'),
                'suffix' => env('SQS_SUFFIX'),
                'region' => env('AWS_DEFAULT_REGION', 'sa-east-1'),
                'after_commit' => false,
            ],
        ],

        'failed' => [
            'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
            'database' => env('DB_CONNECTION', 'mysql'),
            'table' => 'failed_jobs',
        ]
    ],
];
