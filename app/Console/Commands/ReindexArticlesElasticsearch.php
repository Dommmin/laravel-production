<?php

namespace App\Console\Commands;

use App\Jobs\ReindexArticles;
use App\Services\ElasticsearchService;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Illuminate\Console\Command;

class ReindexArticlesElasticsearch extends Command
{
    protected $signature = 'es:reindex-articles';

    protected $description = 'Creates the articles index with mapping and reindexes articles to Elasticsearch';

    /**
     * @throws AuthenticationException
     * @throws ClientResponseException
     * @throws ServerResponseException
     * @throws MissingParameterException
     */
    public function handle(ElasticsearchService $es): void
    {
        $this->info('Creating index articles with mapping...');
        $es->createArticlesIndex();
        $this->info('Reindexing articles...');
        ReindexArticles::dispatchSync();
        $this->info('Done!');
    }
}
