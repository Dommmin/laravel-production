<?php

namespace App\Http\Controllers;

use App\DTO\ArticleFilterData;
use App\Http\Requests\IndexArticleRequest;
use App\Models\Article;
use App\Services\ArticleSearchService;
use App\Services\CityService;
use App\Services\ElasticsearchService;
use App\Services\TagService;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function __construct(private readonly ElasticsearchService $es, private readonly CityService $cityService, private readonly TagService $tagService) {}

    /**
     * @throws AuthenticationException
     * @throws ServerResponseException
     * @throws ClientResponseException
     */
    public function index(IndexArticleRequest $request, ArticleSearchService $searchService): Response
    {
        $filters = ArticleFilterData::from($request->validated());
        $result = $searchService->search($filters);

        return Inertia::render('home', array_merge($result, [
            'cities' => $this->cityService->getAvailableCities(),
            'tags' => $this->tagService->getAvailableTags(),
        ])
        );
    }

    public function show(Article $article): Response
    {
        return Inertia::render('articles/show', [
            'article' => $article,
        ]);
    }

    /**
     * @throws AuthenticationException
     * @throws ClientResponseException
     * @throws ServerResponseException
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
     * @throws AuthenticationException
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
