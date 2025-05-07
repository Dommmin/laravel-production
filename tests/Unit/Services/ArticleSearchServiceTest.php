<?php

namespace Tests\Unit\Services;

use App\DTO\ArticleFilterData;
use App\Services\ArticleSearchService;
use App\Services\ElasticsearchService;
use Mockery;
use Tests\TestCase;

class ArticleSearchServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_basic_search_returns_expected_structure()
    {
        $mockClient = Mockery::mock();
        $mockClient->shouldReceive('search')->andReturn([
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
        ]);
        $mockEs = Mockery::mock(ElasticsearchService::class);
        $mockEs->shouldReceive('client')->andReturn($mockClient);
        $service = new ArticleSearchService($mockEs);
        $filters = new ArticleFilterData(q: 'Test');
        $result = $service->search($filters);
        $this->assertArrayHasKey('articles', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('filters', $result);
        $this->assertEquals('Test title', $result['articles'][0]['title']);
        $this->assertEquals(1, $result['total']);
    }

    public function test_search_by_tag_returns_only_tagged_articles()
    {
        $mockClient = Mockery::mock();
        $mockClient->shouldReceive('search')->andReturn([
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
        ]);
        $mockEs = Mockery::mock(ElasticsearchService::class);
        $mockEs->shouldReceive('client')->andReturn($mockClient);
        $service = new ArticleSearchService($mockEs);
        $filters = new ArticleFilterData(tag: 'laravel');
        $result = $service->search($filters);
        $this->assertCount(1, $result['articles']);
        $this->assertEquals('laravel', $result['articles'][0]['tags'][0]);
    }

    public function test_search_by_city_returns_only_city_articles()
    {
        $mockClient = Mockery::mock();
        $mockClient->shouldReceive('search')->andReturn([
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
        ]);
        $mockEs = Mockery::mock(ElasticsearchService::class);
        $mockEs->shouldReceive('client')->andReturn($mockClient);
        $service = new ArticleSearchService($mockEs);
        $filters = new ArticleFilterData(city: 'Kraków');
        $result = $service->search($filters);
        $this->assertCount(1, $result['articles']);
        $this->assertEquals('Kraków', $result['articles'][0]['city_name']);
    }

    public function test_geo_search_returns_city_within_radius()
    {
        $mockClient = Mockery::mock();
        $mockClient->shouldReceive('search')->andReturn([
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
        ]);
        $mockEs = Mockery::mock(ElasticsearchService::class);
        $mockEs->shouldReceive('client')->andReturn($mockClient);
        $service = new ArticleSearchService($mockEs);
        $filters = new ArticleFilterData(lat: 51.1079, lon: 17.0385, radius: 300);
        $result = $service->search($filters);
        $this->assertCount(1, $result['articles']);
        $this->assertEquals('Kraków', $result['articles'][0]['city_name']);
    }
}
