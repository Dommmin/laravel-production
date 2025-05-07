<?php

namespace Tests\Unit\Services;

use App\Services\ArticleSearchService;
use App\Services\CityService;
use App\Services\ElasticsearchService;
use App\Services\TagService;
use Mockery;
use PHPUnit\Framework\TestCase;

class ArticleSearchServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_search_returns_expected_structure()
    {
        $mockEs = Mockery::mock(ElasticsearchService::class);
        $mockEs->shouldReceive('client->search')->andReturnUsing(function ($params) {
            if (isset($params['size']) && $params['size'] === 0) {
                // agregacje
                return [
                    'aggregations' => [
                        'cities' => ['buckets' => [['key' => 'Warszawa', 'doc_count' => 1]]],
                        'tags' => ['buckets' => [['key' => 'laravel', 'doc_count' => 1]]],
                    ],
                ];
            }

            // wyniki wyszukiwania
            return [
                'hits' => [
                    'hits' => [
                        ['_source' => [
                            'title' => 'Test title',
                            'tags' => ['laravel'],
                            'city_name' => 'Warszawa',
                        ]],
                    ],
                    'total' => ['value' => 1],
                ],
            ];
        });

        $mockCity = Mockery::mock(CityService::class);
        $mockCity->shouldReceive('getAvailableCities')->andReturn(['Warszawa', 'Kraków']);
        $mockTag = Mockery::mock(TagService::class);
        $mockTag->shouldReceive('getAvailableTags')->andReturn(['laravel', 'php']);

        $service = new ArticleSearchService($mockEs, $mockCity, $mockTag);
        $result = $service->search(['q' => 'Test']);

        $this->assertArrayHasKey('articles', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('filters', $result);
        $this->assertArrayHasKey('cities', $result);
        $this->assertArrayHasKey('tags', $result);
        $this->assertEquals('Test title', $result['articles'][0]['title']);
        $this->assertEquals(['Warszawa', 'Kraków'], $result['cities']);
        $this->assertEquals(['laravel', 'php'], $result['tags']);
    }

    public function test_search_by_tag_returns_only_tagged_articles()
    {
        $mockEs = Mockery::mock(ElasticsearchService::class);
        $mockEs->shouldReceive('client->search')->andReturnUsing(function ($params) {
            // Wyniki wyszukiwania po tagu
            return [
                'hits' => [
                    'hits' => [
                        ['_source' => [
                            'title' => 'Artykuł Laravel',
                            'tags' => ['laravel'],
                            'city_name' => 'Warszawa',
                        ]],
                    ],
                    'total' => ['value' => 1],
                ],
            ];
        });
        $mockCity = Mockery::mock(CityService::class);
        $mockCity->shouldReceive('getAvailableCities')->andReturn(['Warszawa']);
        $mockTag = Mockery::mock(TagService::class);
        $mockTag->shouldReceive('getAvailableTags')->andReturn(['laravel']);
        $service = new ArticleSearchService($mockEs, $mockCity, $mockTag);
        $result = $service->search(['tag' => 'laravel']);
        $this->assertCount(1, $result['articles']);
        $this->assertEquals('laravel', $result['articles'][0]['tags'][0]);
    }

    public function test_search_by_city_returns_only_city_articles()
    {
        $mockEs = Mockery::mock(ElasticsearchService::class);
        $mockEs->shouldReceive('client->search')->andReturnUsing(function ($params) {
            // Wyniki wyszukiwania po mieście
            return [
                'hits' => [
                    'hits' => [
                        ['_source' => [
                            'title' => 'Artykuł z Krakowa',
                            'tags' => ['php'],
                            'city_name' => 'Kraków',
                        ]],
                    ],
                    'total' => ['value' => 1],
                ],
            ];
        });
        $mockCity = Mockery::mock(CityService::class);
        $mockCity->shouldReceive('getAvailableCities')->andReturn(['Kraków']);
        $mockTag = Mockery::mock(TagService::class);
        $mockTag->shouldReceive('getAvailableTags')->andReturn(['php']);
        $service = new ArticleSearchService($mockEs, $mockCity, $mockTag);
        $result = $service->search(['city' => 'Kraków']);
        $this->assertCount(1, $result['articles']);
        $this->assertEquals('Kraków', $result['articles'][0]['city_name']);
    }

    public function test_geo_search_returns_city_within_radius()
    {
        $mockEs = Mockery::mock(ElasticsearchService::class);
        $mockEs->shouldReceive('client->search')->andReturnUsing(function ($params) {
            // Sprawdź, czy zapytanie zawiera geo_distance z odpowiednimi współrzędnymi i promieniem
            $geo = $params['body']['query']['bool']['filter'][0]['geo_distance'] ?? null;
            if ($geo && $geo['distance'] === '300km' && abs($geo['location']['lat'] - 51.1079) < 0.01 && abs($geo['location']['lon'] - 17.0385) < 0.01) {
                // Zwróć artykuł z Krakowa
                return [
                    'hits' => [
                        'hits' => [
                            ['_source' => [
                                'title' => 'Artykuł z Krakowa',
                                'tags' => ['php'],
                                'city_name' => 'Kraków',
                                'location' => ['lat' => 50.0647, 'lon' => 19.9450],
                            ]],
                        ],
                        'total' => ['value' => 1],
                    ],
                ];
            }

            // Domyślnie pusta lista
            return [
                'hits' => [
                    'hits' => [],
                    'total' => ['value' => 0],
                ],
            ];
        });
        $mockCity = Mockery::mock(CityService::class);
        $mockCity->shouldReceive('getAvailableCities')->andReturn(['Kraków']);
        $mockTag = Mockery::mock(TagService::class);
        $mockTag->shouldReceive('getAvailableTags')->andReturn(['php']);
        $service = new ArticleSearchService($mockEs, $mockCity, $mockTag);
        $result = $service->search([
            'lat' => 51.1079, // Wrocław
            'lon' => 17.0385,
            'radius' => 300,
        ]);
        $this->assertCount(1, $result['articles']);
        $this->assertEquals('Kraków', $result['articles'][0]['city_name']);
    }
}
