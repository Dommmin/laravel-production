<?php

namespace Tests\Unit\Services;

use App\Services\CityService;
use PHPUnit\Framework\TestCase;
use Mockery;
use App\Models\Article;

class CityServiceTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_available_cities_returns_cities()
    {
        $mock = Mockery::mock('alias:' . Article::class);
        $mock->shouldReceive('query->distinct->pluck->all')->andReturn(['Warszawa', 'Kraków']);
        $service = new CityService();
        $this->assertEquals(['Warszawa', 'Kraków'], $service->getAvailableCities());
    }
} 