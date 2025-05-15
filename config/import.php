<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Import Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure various aspects of the import system.
    |
    */

    'max_errors_to_show' => env('IMPORT_MAX_ERRORS', 3),
    'chunk_size' => env('IMPORT_CHUNK_SIZE', 1000),
    'timeout' => env('IMPORT_TIMEOUT', 600),
    'queue' => [
        'connection' => env('IMPORT_QUEUE_CONNECTION', 'redis'),
        'queue' => env('IMPORT_QUEUE', 'imports'),
    ],
];
