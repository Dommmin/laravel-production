<?php

namespace App\Observers;

use App\Models\Article;
use App\Services\ElasticsearchService;

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
     */
    public function deleted(Article $article): void
    {
        $es = app(ElasticsearchService::class)->client();
        $es->delete([
            'index' => 'articles',
            'id'    => $article->id,
        ]);
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
        $es = app(ElasticsearchService::class)->client();
        $es->index([
            'index' => 'articles',
            'id'    => $article->id,
            'body'  => [
                'title'        => $article->title,
                'content'      => $article->content,
                'user'         => $article->user?->name,
                'tags'         => $article->tags->pluck('name')->toArray(),
                'location'     => $article->location,
                'city_name'    => $article->city_name ?? null,
            ]
        ]);
    }
}
