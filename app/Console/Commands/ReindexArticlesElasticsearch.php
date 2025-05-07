<?php

namespace App\Console\Commands;

use App\Jobs\ReindexArticles;
use App\Services\ElasticsearchService;
use Illuminate\Console\Command;

class ReindexArticlesElasticsearch extends Command
{
    protected $signature = 'es:reindex-articles';

    protected $description = 'Creates the articles index with mapping and reindexes articles to Elasticsearch';

    public function handle(ElasticsearchService $es): void
    {
        $this->info('Creating index articles with mapping...');
        $es->createArticlesIndex();
        $this->info('Reindexing articles...');
        ReindexArticles::dispatchSync();
        $this->info('Done!');
    }
}
