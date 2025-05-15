<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\ArticleFilterData;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Illuminate\Support\Arr;

readonly class ArticleSearchService
{
    public function __construct(private ElasticsearchService $elasticsearchService) {}

    /**
     * @throws AuthenticationException
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function search(ArticleFilterData $articleFilterData): array
    {
        $queryBody = [
            'index' => 'articles',
            'from' => ($articleFilterData->page - 1) * $articleFilterData->size,
            'size' => $articleFilterData->size,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => $this->buildMustQueries($articleFilterData),
                        'filter' => $this->buildFilterQueries($articleFilterData),
                    ],
                ],
            ],
        ];

        $results = $this->elasticsearchService->search($queryBody);

        return [
            'articles' => collect($results['hits']['hits'])->pluck('_source')->all(),
            'total' => $results['hits']['total']['value'] ?? 0,
            'filters' => $articleFilterData->toArray(),
        ];
    }

    private function buildMustQueries(ArticleFilterData $articleFilterData): array
    {
        $must = [];

        if ($articleFilterData->q) {
            $must[] = mb_strlen($articleFilterData->q) <= 2
                ? ['wildcard' => ['title' => "*{$articleFilterData->q}*"]]
                : [
                    'multi_match' => [
                        'query' => $articleFilterData->q,
                        'fields' => ['title^2', 'tags'],
                        'fuzziness' => 'auto',
                        'operator' => 'and',
                        'minimum_should_match' => '100%',
                    ],
                ];
        }

        if ($articleFilterData->tag) {
            $must[] = ['term' => ['tags' => $articleFilterData->tag]];
        }

        return $must;
    }

    private function buildFilterQueries(ArticleFilterData $articleFilterData): array
    {
        $filter = [];

        if ($articleFilterData->city) {
            $filter[] = ['term' => ['city_name' => $articleFilterData->city]];
        }

        if ($articleFilterData->lat && $articleFilterData->lon && $articleFilterData->radius > 0) {
            $filter = array_filter($filter, fn ($f): bool => ! Arr::has($f, 'term.city_name'));
            $filter[] = [
                'geo_distance' => [
                    'distance' => "{$articleFilterData->radius}km",
                    'location' => [
                        'lat' => $articleFilterData->lat,
                        'lon' => $articleFilterData->lon,
                    ],
                ],
            ];
        }

        return $filter;
    }
}
