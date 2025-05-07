<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ElasticsearchService;
use App\Jobs\ReindexArticlesToElasticsearch;

class ReindexArticlesElasticsearch extends Command
{
    protected $signature = 'es:reindex-articles';
    protected $description = 'Tworzy indeks articles z mappingiem i reindeksuje artykuły do Elasticsearch';

    public function handle(ElasticsearchService $es)
    {
        $this->info('Tworzę indeks articles z mappingiem...');
        $es->createArticlesIndex();
        $this->info('Reindeksuję artykuły...');
        dispatch_sync(new ReindexArticlesToElasticsearch());
        $this->info('Gotowe!');
    }
} 