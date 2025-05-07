<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\ArticleSearchService;
use App\Services\ElasticsearchService;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function __construct(private readonly ElasticsearchService $es) {}

    public function index(Request $request, ArticleSearchService $searchService): Response
    {
        $result = $searchService->search($request->all());

        return Inertia::render('home', $result);
    }

    public function show(Article $article): Response
    {
        return Inertia::render('articles/show', [
            'article' => $article,
        ]);
    }

    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     */
    public function similar(Request $request, Article $article): JsonResponse
    {
        $client = $this->es->client();
        $response = $client->search([
            'index' => 'articles',
            'body' => [
                'query' => [
                    'more_like_this' => [
                        'fields' => ['title', 'content', 'tags'],
                        'like' => [
                            [
                                '_index' => 'articles',
                                '_id' => $article->id,
                            ],
                        ],
                        'min_term_freq' => 1,
                        'max_query_terms' => 12,
                    ],
                ],
                'size' => 5,
            ],
        ]);

        $ids = collect($response['hits']['hits'])->pluck('_id');
        $similar = Article::whereIn('id', $ids)->get();

        return response()->json($similar);
    }

    /**
     * @throws ServerResponseException
     * @throws ClientResponseException
     */
    public function cityAggregation(): JsonResponse
    {
        $client = $this->es->client();
        $response = $client->search([
            'index' => 'articles',
            'body' => [
                'size' => 0,
                'aggs' => [
                    'articles_per_city' => [
                        'terms' => [
                            'field' => 'city_name',
                            'size' => 20,
                        ],
                    ],
                ],
            ],
        ]);

        $buckets = $response['aggregations']['articles_per_city']['buckets'] ?? [];

        return response()->json($buckets);
    }
}
