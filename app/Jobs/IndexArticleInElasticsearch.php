<?php

namespace App\Jobs;

use App\Models\Article;
use App\Services\ElasticsearchService;

class IndexArticleInElasticsearch
{
    public $article;

    public function __construct(Article $article)
    {
        $this->article = $article;
    }

    public function handle(ElasticsearchService $es)
    {
        $es->index([
            'index' => 'articles',
            'id'    => $this->article->id,
            'body'  => [
                'title'      => $this->article->title,
                'content'    => $this->article->content,
                'user'       => $this->article->user?->name,
                'tags'       => $this->article->tags->pluck('name')->toArray(),
                'location'   => $this->article->location,
                'city_name'  => $this->article->city_name,
            ]
        ]);
    }
}
