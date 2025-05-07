<?php

namespace App\Services;

use App\DTO\ArticleFilterData;
use Elastic\Elasticsearch\Exception\AuthenticationException;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Illuminate\Support\Arr;

readonly class ArticleSearchService
{
    public function __construct(private ElasticsearchService $es) {}

    /**
     * @throws AuthenticationException
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function search(ArticleFilterData $filters): array
    {
        $queryBody = [
            'index' => 'articles',
            'from' => ($filters->page - 1) * $filters->size,
            'size' => $filters->size,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => $this->buildMustQueries($filters),
                        'filter' => $this->buildFilterQueries($filters),
                    ],
                ],
            ],
        ];

        $results = $this->es->client()->search($queryBody);

        return [
            'articles' => collect($results['hits']['hits'])->pluck('_source')->all(),
            'total' => $results['hits']['total']['value'] ?? 0,
            'filters' => $filters->toArray(),
        ];
    }

    private function buildMustQueries(ArticleFilterData $filters): array
    {
        $must = [];

        if ($filters->q) {
            $must[] = mb_strlen($filters->q) <= 2
                ? ['wildcard' => ['title' => "*{$filters->q}*"]]
                : [
                    'multi_match' => [
                        'query' => $filters->q,
                        'fields' => ['title^2', 'tags'],
                        'fuzziness' => 'auto',
                        'operator' => 'and',
                        'minimum_should_match' => '100%',
                    ],
                ];
        }

        if ($filters->tag) {
            $must[] = ['term' => ['tags' => $filters->tag]];
        }

        return $must;
    }

    private function buildFilterQueries(ArticleFilterData $filters): array
    {
        $filter = [];

        if ($filters->city) {
            $filter[] = ['term' => ['city_name' => $filters->city]];
        }

        if ($filters->lat && $filters->lon && $filters->radius > 0) {
            $filter = array_filter($filter, fn ($f) => ! Arr::has($f, 'term.city_name'));
            $filter[] = [
                'geo_distance' => [
                    'distance' => "{$filters->radius}km",
                    'location' => [
                        'lat' => $filters->lat,
                        'lon' => $filters->lon,
                    ],
                ],
            ];
        }

        return $filter;
    }
}
