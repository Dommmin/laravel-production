<?php

namespace App\Services;

use Illuminate\Support\Arr;

/**
 * Serwis do wyszukiwania artykułów w Elasticsearch z filtrami, paginacją i agregacjami
 */
class ArticleSearchService
{
    protected ElasticsearchService $es;

    protected CityService $cityService;

    protected TagService $tagService;

    public function __construct(ElasticsearchService $es, CityService $cityService, TagService $tagService)
    {
        $this->es = $es;
        $this->cityService = $cityService;
        $this->tagService = $tagService;
    }

    /**
     * Wyszukiwanie artykułów z agregacjami, filtrami, paginacją.
     */
    public function search(array $params): array
    {
        $query = Arr::get($params, 'q');
        $tag = Arr::get($params, 'tag');
        $city = Arr::get($params, 'city');
        $page = (int) Arr::get($params, 'page', 1);
        $size = (int) Arr::get($params, 'size', 20);
        $from = ($page - 1) * $size;
        $radius = (int) Arr::get($params, 'radius', 0);
        $lat = Arr::get($params, 'lat');
        $lon = Arr::get($params, 'lon');

        $must = [];
        $filter = [];

        if ($query) {
            if (mb_strlen($query) <= 2) {
                $must[] = [
                    'wildcard' => [
                        'title' => "*{$query}*",
                    ],
                ];
            } else {
                $must[] = [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => ['title^2', 'tags'],
                        'fuzziness' => 'auto',
                        'operator' => 'and',
                        'minimum_should_match' => '100%',
                    ],
                ];
            }
        }

        if ($tag) {
            $must[] = [
                'term' => ['tags' => $tag],
            ];
        }

        if ($city) {
            $filter[] = [
                'term' => ['city_name' => $city],
            ];
        }

        if ($lat && $lon && $radius > 0) {
            $filter = array_filter($filter, function ($f) {
                return ! Arr::has($f, 'term.city_name');
            });
            $filter[] = [
                'geo_distance' => [
                    'distance' => $radius.'km',
                    'location' => [
                        'lat' => (float) $lat,
                        'lon' => (float) $lon,
                    ],
                ],
            ];
        }

        $results = $this->es->client()->search([
            'index' => 'articles',
            'from' => $from,
            'size' => $size,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => $must,
                        'filter' => $filter,
                    ],
                ],
            ],
        ]);

        $articles = collect($results['hits']['hits'])->map(function ($hit) {
            return $hit['_source'];
        })->all();
        $total = $results['hits']['total']['value'] ?? 0;

        // Agregacje miast i tagów
        $aggResults = $this->es->client()->search([
            'index' => 'articles',
            'size' => 0,
            'body' => [
                'aggs' => [
                    'cities' => [
                        'terms' => [
                            'field' => 'city_name.keyword',
                            'size' => 100,
                        ],
                    ],
                    'tags' => [
                        'terms' => [
                            'field' => 'tags.keyword',
                            'size' => 100,
                        ],
                    ],
                ],
            ],
        ]);

        $cities = $this->cityService->getAvailableCities();
        $tags = $this->tagService->getAvailableTags();

        return [
            'articles' => $articles,
            'total' => $total,
            'filters' => [
                'q' => $query,
                'tag' => $tag,
                'city' => $city,
                'radius' => $radius,
                'lat' => $lat,
                'lon' => $lon,
                'page' => $page,
            ],
            'cities' => $cities,
            'tags' => $tags,
            // Możesz dodać tu też agregacje jeśli chcesz je wyświetlać na froncie
            // 'agg_cities' => $aggResults['aggregations']['cities']['buckets'] ?? [],
            // 'agg_tags' => $aggResults['aggregations']['tags']['buckets'] ?? [],
        ];
    }
}
