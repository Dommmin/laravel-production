<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * These cron jobs are run in the Artisan command line when a command is executed.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire:inspire')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');

        // Dodaj komendę do utworzenia indeksu i reindeksacji
        $this->command('es:reindex-articles', function () {
            $es = app(\App\Services\ElasticsearchService::class);
            $es->createArticlesIndex();
            dispatch_sync(new \App\Jobs\ReindexArticlesToElasticsearch());
            $this->info('Indeks articles utworzony i zreindeksowany!');
        });
    }
} 