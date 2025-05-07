<?php

namespace App\Jobs;

use App\Models\Article;
use App\Services\ElasticsearchService;

class ReindexArticlesToElasticsearch
{
    public function handle(ElasticsearchService $es)
    {
        Article::with(['user', 'tags'])->chunk(100, function ($articles) use ($es) {
            foreach ($articles as $article) {
                $es->client()->index([
                    'index' => 'articles',
                    'id'    => $article->id,
                    'body'  => [
                        'title'    => $article->title,
                        'content'  => $article->content,
                        'user'     => $article->user?->name,
                        'tags'     => $article->tags->pluck('name')->toArray(),
                        'location' => $article->location,
                        'city_name'=> $article->city_name ?? null,
                    ]
                ]);
            }
        });
    }
}
