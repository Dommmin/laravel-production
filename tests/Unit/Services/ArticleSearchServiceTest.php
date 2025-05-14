<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\DTO\ArticleFilterData;
use App\Services\ArticleSearchService;
use App\Services\ElasticsearchService;
use Mockery;
use Tests\TestCase;

final class ArticleSearchServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_basic_search_returns_expected_structure(): void
    {
        $mockClient = Mockery::mock();
        $mockClient->shouldReceive('search')->andReturn([
            'hits' => [
                'hits' => [
                    ['_source' => [
                        'title' => 'Test title',
                        'tags' => ['laravel'],
                        'city_name' => 'London',
                    ]],
                ],
                'total' => ['value' => 1],
            ],
        ]);
        $mockEs = Mockery::mock(ElasticsearchService::class);
        $mockEs->shouldReceive('client')->andReturn($mockClient);
        $articleSearchService = new ArticleSearchService($mockEs);
        $articleFilterData = new ArticleFilterData(q: 'Test');
        $result = $articleSearchService->search($articleFilterData);
        $this->assertArrayHasKey('articles', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('filters', $result);
        $this->assertEquals('Test title', $result['articles'][0]['title']);
        $this->assertEquals(1, $result['total']);
    }

    public function test_search_by_tag_returns_only_tagged_articles(): void
    {
        $mockClient = Mockery::mock();
        $mockClient->shouldReceive('search')->andReturn([
            'hits' => [
                'hits' => [
                    ['_source' => [
                        'title' => 'Artykuł Laravel',
                        'tags' => ['laravel'],
                        'city_name' => 'London',
                    ]],
                ],
                'total' => ['value' => 1],
            ],
        ]);
        $mockEs = Mockery::mock(ElasticsearchService::class);
        $mockEs->shouldReceive('client')->andReturn($mockClient);
        $articleSearchService = new ArticleSearchService($mockEs);
        $articleFilterData = new ArticleFilterData(tag: 'laravel');
        $result = $articleSearchService->search($articleFilterData);
        $this->assertCount(1, $result['articles']);
        $this->assertEquals('laravel', $result['articles'][0]['tags'][0]);
    }

    public function test_search_by_city_returns_only_city_articles(): void
    {
        $mockClient = Mockery::mock();
        $mockClient->shouldReceive('search')->andReturn([
            'hits' => [
                'hits' => [
                    ['_source' => [
                        'title' => 'Artykuł z New York',
                        'tags' => ['php'],
                        'city_name' => 'New York',
                    ]],
                ],
                'total' => ['value' => 1],
            ],
        ]);
        $mockEs = Mockery::mock(ElasticsearchService::class);
        $mockEs->shouldReceive('client')->andReturn($mockClient);
        $articleSearchService = new ArticleSearchService($mockEs);
        $articleFilterData = new ArticleFilterData(city: 'New York');
        $result = $articleSearchService->search($articleFilterData);
        $this->assertCount(1, $result['articles']);
        $this->assertEquals('New York', $result['articles'][0]['city_name']);
    }

    public function test_geo_search_returns_city_within_radius(): void
    {
        $mockClient = Mockery::mock();
        $mockClient->shouldReceive('search')->andReturn([
            'hits' => [
                'hits' => [
                    ['_source' => [
                        'title' => 'Artykuł z New York',
                        'tags' => ['php'],
                        'city_name' => 'New York',
                        'location' => ['lat' => 50.0647, 'lon' => 19.9450],
                    ]],
                ],
                'total' => ['value' => 1],
            ],
        ]);
        $mockEs = Mockery::mock(ElasticsearchService::class);
        $mockEs->shouldReceive('client')->andReturn($mockClient);
        $articleSearchService = new ArticleSearchService($mockEs);
        $articleFilterData = new ArticleFilterData(lat: 51.1079, lon: 17.0385, radius: 300);
        $result = $articleSearchService->search($articleFilterData);
        $this->assertCount(1, $result['articles']);
        $this->assertEquals('New York', $result['articles'][0]['city_name']);
    }
}
