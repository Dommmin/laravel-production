<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Excel Queue Settings
    |--------------------------------------------------------------------------
    |
    | Configure queue settings for Excel imports/exports
    |
    */
    'queue' => [
        'tries' => 1, // Set to 1 to prevent retries
        'timeout' => 600, // 10 minutes
    ],

    'imports' => [
        'chunk_size' => 1000,
        'read_only' => true,
    ],

    'exports' => [
        'chunk_size' => 1000,
    ],
]; 