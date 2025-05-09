<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Services\CityService;
use Mockery;
use PHPUnit\Framework\TestCase;

class CityServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_available_cities_returns_cities()
    {
        $mock = Mockery::mock('alias:'.Article::class);
        $mock->shouldReceive('query->distinct->pluck->all')->andReturn(['London', 'New York']);
        $service = new CityService;
        $this->assertEquals(['London', 'New York'], $service->getAvailableCities());
    }
}
