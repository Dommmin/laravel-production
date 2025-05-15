<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\ReindexArticles;
use App\Models\Article;
use App\Services\ElasticsearchService;
use Exception;
use Log;

class ArticleObserver
{
    public function deleted(Article $article): void
    {
        $client = app(ElasticsearchService::class)->client();

        try {
            $client->delete([
                'index' => 'articles',
                'id' => (string) $article->id,
            ]);
        } catch (Exception $e) {
            Log::info($e->getMessage());
        }
    }

    public function saved(Article $article): void
    {
        ReindexArticles::dispatch();
    }
}
