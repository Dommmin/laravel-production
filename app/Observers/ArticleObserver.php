<?php

namespace App\Observers;

use App\Jobs\ReindexArticles;
use App\Models\Article;
use App\Services\ElasticsearchService;

class ArticleObserver
{
    public function deleted(Article $article): void
    {
        $es = app(ElasticsearchService::class)->client();

        try {
            $es->delete([
                'index' => 'articles',
                'id' => $article->id,
            ]);
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
        }
    }

    public function saved(Article $article)
    {
        ReindexArticles::dispatch();
    }
}
