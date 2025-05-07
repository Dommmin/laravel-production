<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Jobs\IndexArticleInElasticsearch;

class ArticleController extends Controller
{
    public function create()
    {
        $tags = \App\Models\Tag::all()->map(fn($t) => [
            'label' => $t->name,
            'value' => $t->name,
        ]);
        return Inertia::render('articles/create', [
            'tags' => $tags,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'city_name' => 'required|string',
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
            'tags' => 'array',
            'tags.*' => 'string|exists:tags,name',
        ]);
        $data['user_id'] = auth()->id();
        // Jeśli city_name nie jest przekazane, spróbuj pobrać z Nominatim (opcjonalnie)
        $article = Article::create($data);
        if (!empty($data['tags'])) {
            $article->tags()->sync(
                \App\Models\Tag::whereIn('name', $data['tags'])->pluck('id')->all()
            );
        }
        IndexArticleInElasticsearch::dispatch($article);
        return redirect()->route('dashboard')->with('success', 'Artykuł dodany!');
    }

    public function similar(Request $request, Article $article)
    {
        $es = app(\App\Services\ElasticsearchService::class)->client();
        $response = $es->search([
            'index' => 'articles',
            'body' => [
                'query' => [
                    'more_like_this' => [
                        'fields' => ['title', 'content', 'tags'],
                        'like' => [
                            [
                                '_index' => 'articles',
                                '_id' => $article->id,
                            ]
                        ],
                        'min_term_freq' => 1,
                        'max_query_terms' => 12,
                    ]
                ],
                'size' => 5,
            ]
        ]);
        $ids = collect($response['hits']['hits'])->pluck('_id');
        $similar = \App\Models\Article::whereIn('id', $ids)->get();
        return response()->json($similar);
    }

    public function cityAggregation()
    {
        $es = app(\App\Services\ElasticsearchService::class)->client();
        $response = $es->search([
            'index' => 'articles',
            'body' => [
                'size' => 0,
                'aggs' => [
                    'articles_per_city' => [
                        'terms' => [
                            'field' => 'city_name.keyword',
                            'size' => 20
                        ]
                    ]
                ]
            ]
        ]);
        $buckets = $response['aggregations']['articles_per_city']['buckets'] ?? [];
        return response()->json($buckets);
    }
}
