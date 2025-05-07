<?php

namespace Tests\Unit\Services;

use App\Services\TagService;
use PHPUnit\Framework\TestCase;
use Mockery;
use App\Models\Tag;

class TagServiceTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_available_tags_returns_tags()
    {
        $mock = Mockery::mock('alias:' . Tag::class);
        $mock->shouldReceive('query->pluck->all')->andReturn(['laravel', 'php']);
        $service = new TagService();
        $this->assertEquals(['laravel', 'php'], $service->getAvailableTags());
    }
} 