<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Services\CityService;
use Mockery;
use PHPUnit\Framework\TestCase;

final class CityServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_available_cities_returns_cities(): void
    {
        $mock = Mockery::mock('alias:'.Article::class);
        $mock->shouldReceive('query->distinct->pluck->all')->andReturn(['London', 'New York']);
        $cityService = new CityService;
        $this->assertEquals(['London', 'New York'], $cityService->getAvailableCities());
    }
}
