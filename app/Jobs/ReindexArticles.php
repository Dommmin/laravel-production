<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Article;
use App\Models\User;
use App\Services\ElasticsearchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ReindexArticles implements ShouldQueue
{
    use Queueable;

    public function handle(ElasticsearchService $elasticsearchService): void
    {
        Article::with(['user', 'tags'])->chunk(100, function ($articles) use ($elasticsearchService): void {
            foreach ($articles as $article) {

                /** @var User $user */
                $user = $article->user;

                $elasticsearchService->index([
                    'index' => 'articles',
                    'id' => $article->id,
                    'body' => [
                        'title' => $article->title,
                        'slug' => $article->slug,
                        'content' => $article->content,
                        'user' => $user->name,
                        'tags' => $article->tags->pluck('name')->toArray(),
                        'location' => $article->location ?? null,
                        'city_name' => $article->city_name ?? null,
                    ],
                ]);
            }
        });
    }
}
