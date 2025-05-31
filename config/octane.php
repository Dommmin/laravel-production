<?php

use Laravel\Octane\Contracts\OperationTerminated;
use Laravel\Octane\Events\RequestHandled;
use Laravel\Octane\Events\RequestReceived;
use Laravel\Octane\Events\RequestTerminated;
use Laravel\Octane\Events\TaskReceived;
use Laravel\Octane\Events\TaskTerminated;
use Laravel\Octane\Events\TickReceived;
use Laravel\Octane\Events\TickTerminated;
use Laravel\Octane\Events\WorkerErrorOccurred;
use Laravel\Octane\Events\WorkerStarting;
use Laravel\Octane\Events\WorkerStopping;
use Laravel\Octane\Listeners\CloseMonologHandlers;
use Laravel\Octane\Listeners\CollectGarbage;
use Laravel\Octane\Listeners\DisconnectFromDatabases;
use Laravel\Octane\Listeners\EnsureUploadedFilesAreValid;
use Laravel\Octane\Listeners\EnsureUploadedFilesCanBeMoved;
use Laravel\Octane\Listeners\FlushOnce;
use Laravel\Octane\Listeners\FlushTemporaryContainerInstances;
use Laravel\Octane\Listeners\FlushUploadedFiles;
use Laravel\Octane\Listeners\ReportException;
use Laravel\Octane\Listeners\StopWorkerIfNecessary;
use Laravel\Octane\Octane;

return [

    /*
    |--------------------------------------------------------------------------
    | Octane Server
    |--------------------------------------------------------------------------
    |
    | This value determines the default "server" that will be used by Octane
    | when starting, restarting, or stopping your server via the CLI. You
    | are free to modify this value based on your needs.
    |
    */

    'server' => env('OCTANE_SERVER', 'swoole'),

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS
    |--------------------------------------------------------------------------
    |
    | When this configuration value is set to "true", Octane will inform the
    | framework that all absolute links must be generated using the HTTPS
    | protocol. Otherwise your links may be generated using plain HTTP.
    |
    */

    'https' => env('OCTANE_HTTPS', false),

    /*
    |--------------------------------------------------------------------------
    | Octane HTTP Server Options
    |--------------------------------------------------------------------------
    |
    | While you are free to modify these HTTP server options, remember, these
    | are the default settings that Octane will use if you are not using
    | --host and --port flags when starting or restarting the server.
    |
    */

    'host' => env('OCTANE_HOST', '0.0.0.0'),
    'port' => env('OCTANE_PORT', '8000'),

    /*
    |--------------------------------------------------------------------------
    | Maximum Number Of Workers
    |--------------------------------------------------------------------------
    |
    | This value determines the maximum number of workers that should be
    | started when starting the server with --workers. Note that this
    | value should respect the memory limits of your server.
    |
    */

    'workers' => env('OCTANE_WORKERS', 4),

    /*
    |--------------------------------------------------------------------------
    | Maximum Number Of Task Workers
    |--------------------------------------------------------------------------
    |
    | This value determines the maximum number of "task" workers that should be
    | started when starting the server with --workers. Note that this
    | value should respect the memory limits of your server.
    |
    */

    'task_workers' => env('OCTANE_TASK_WORKERS', 2),

    /*
    |--------------------------------------------------------------------------
    | Maximum Number Of Requests Per Worker
    |--------------------------------------------------------------------------
    |
    | This value determines the "graceful" reload of a worker. When set, worker
    | will reload after handling that number of requests; otherwise, workers
    | will live forever and handle requests until note are left to be handled
    | in the queue, and then reload.
    |
    */

    'max_requests' => env('OCTANE_MAX_REQUESTS', 1000),

    /*
    |--------------------------------------------------------------------------
    | Memory Limit
    |--------------------------------------------------------------------------
    |
    | This value determines the maximum memory limit that will be used when
    | starting the server with --memory-limit. Note that this value should
    | respect the memory limits of your server.
    |
    */

    'memory_limit' => env('OCTANE_MEMORY_LIMIT', 128),

    /*
    |--------------------------------------------------------------------------
    | Queue Workers
    |--------------------------------------------------------------------------
    |
    | This value determines the number of queue workers that should be started
    | when starting the server with --queue-workers. Note that this value
    | should respect the memory limits of your server.
    |
    */

    'queue_workers' => env('OCTANE_QUEUE_WORKERS', 1),

    /*
    |--------------------------------------------------------------------------
    | Queue Timeout
    |--------------------------------------------------------------------------
    |
    | This value determines the number of seconds that queue workers should
    | wait for a job to process before timing out and restarting.
    |
    */

    'queue_timeout' => env('OCTANE_QUEUE_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Swoole HTTP Server Options
    |--------------------------------------------------------------------------
    |
    | Here you may configure the Swoole HTTP server options. See the Swoole
    | documentation for more information on these settings.
    |
    */

    'swoole' => [
        'options' => [
            'http_compression' => true,
            'http_compression_level' => 6,
            'package_max_length' => 10 * 1024 * 1024,
            'buffer_output_size' => 2 * 1024 * 1024,
            'worker_num' => env('OCTANE_WORKERS', 4),
            'task_worker_num' => env('OCTANE_TASK_WORKERS', 2),
            'max_request' => env('OCTANE_MAX_REQUESTS', 1000),
            'max_conn' => 1000,
            'enable_reuse_port' => true,
            'enable_coroutine' => true,
            'max_coroutine' => 3000,
            'hook_flags' => 0x1FFF,
            'log_level' => 2,
            'display_errors' => 0,
            'enable_stats' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane Listeners
    |--------------------------------------------------------------------------
    |
    | All of the event listeners for Octane's events are defined below. These
    | listeners are responsible for resetting your application's state for
    | the next request. You may even add your own listeners to the list.
    |
    */

    'listeners' => [
        WorkerStarting::class => [
            EnsureUploadedFilesAreValid::class,
            EnsureUploadedFilesCanBeMoved::class,
        ],

        RequestReceived::class => [
            ...Octane::prepareApplicationForNextOperation(),
            ...Octane::prepareApplicationForNextRequest(),
            //
        ],

        RequestHandled::class => [
            //
        ],

        RequestTerminated::class => [
            // FlushUploadedFiles::class,
        ],

        TaskReceived::class => [
            ...Octane::prepareApplicationForNextOperation(),
            //
        ],

        TaskTerminated::class => [
            //
        ],

        TickReceived::class => [
            ...Octane::prepareApplicationForNextOperation(),
            //
        ],

        TickTerminated::class => [
            //
        ],

        OperationTerminated::class => [
            FlushOnce::class,
            FlushTemporaryContainerInstances::class,
            // DisconnectFromDatabases::class,
            // CollectGarbage::class,
        ],

        WorkerErrorOccurred::class => [
            ReportException::class,
            StopWorkerIfNecessary::class,
        ],

        WorkerStopping::class => [
            CloseMonologHandlers::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Warm / Flush Bindings
    |--------------------------------------------------------------------------
    |
    | The bindings below will be pre-warmed when starting the server and they
    | will be flushed before every new request. You may want to monitor some
    | of these bindings in your providers.
    |
    */

    'warm' => [
        ...Octane::defaultServicesToWarm(),
    ],

    'flush' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane Cache Table
    |--------------------------------------------------------------------------
    |
    | While using Swoole, you may leverage the Octane cache, which is powered by
    | a Swoole table. You may set the maximum number of rows as well as the
    | number of bytes per row using the configuration options below.
    |
    */

    'cache' => [
        'rows' => 1000,
        'bytes' => 10000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Octane Tables
    |--------------------------------------------------------------------------
    |
    | While using Swoole, you may define additional tables as required by the
    | application. These tables can be used to store data that needs to be
    | quickly accessed by other workers on the particular server.
    |
    */

    'tables' => [
        'example:1000' => [
            'name' => 'string:1000',
            'votes' => 'int',
        ],
        'app-stats:1000' => [
            'requests_handled' => 'int',
            'last_request_at' => 'string:100',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Watching
    |--------------------------------------------------------------------------
    |
    | The following list of files and directories will be watched when using
    | the --watch option offered by Octane. If any of the directories and
    | files are changed, Octane will automatically reload your workers.
    |
    */

    'watch' => [
        'app',
        'bootstrap',
        'config/**/*.php',
        'database/**/*.php',
        'public/**/*.php',
        'resources/**/*.php',
        'routes',
        'composer.lock',
        '.env',
    ],

    /*
    |--------------------------------------------------------------------------
    | Garbage Collection Threshold
    |--------------------------------------------------------------------------
    |
    | When executing long-lived PHP scripts such as Octane, memory can build
    | up before being cleared by PHP. You can force Octane to run garbage
    | collection if your application consumes this amount of megabytes.
    |
    */

    'garbage' => 50,

    /*
    |--------------------------------------------------------------------------
    | Maximum Execution Time
    |--------------------------------------------------------------------------
    |
    | The following setting configures the maximum execution time for requests
    | being handled by Octane. You may set this value to 0 to indicate that
    | there isn't a specific time limit on Octane request execution time.
    |
    */

    'max_execution_time' => 30,

];
