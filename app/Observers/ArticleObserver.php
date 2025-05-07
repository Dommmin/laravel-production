<?php

namespace App\Observers;

use App\Jobs\ReindexArticles;
use App\Models\Article;
use App\Services\ElasticsearchService;
use Elastic\Elasticsearch\Exception\AuthenticationException;

class ArticleObserver
{
    /**
     * Handle the Article "created" event.
     */
    public function created(Article $article): void
    {
        //
    }

    /**
     * Handle the Article "updated" event.
     */
    public function updated(Article $article): void
    {
        //
    }

    /**
     * Handle the Article "deleted" event.
     *
     * @throws AuthenticationException
     */
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

    /**
     * Handle the Article "restored" event.
     */
    public function restored(Article $article): void
    {
        //
    }

    /**
     * Handle the Article "force deleted" event.
     */
    public function forceDeleted(Article $article): void
    {
        //
    }

    public function saved(Article $article)
    {
        ReindexArticles::dispatch();
    }
}
