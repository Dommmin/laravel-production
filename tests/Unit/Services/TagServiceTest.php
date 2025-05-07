<?php

namespace Tests\Unit\Services;

use App\Models\Tag;
use App\Services\TagService;
use Mockery;
use PHPUnit\Framework\TestCase;

class TagServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_available_tags_returns_tags()
    {
        $mock = Mockery::mock('alias:'.Tag::class);
        $mock->shouldReceive('query->pluck->all')->andReturn(['laravel', 'php']);
        $service = new TagService;
        $this->assertEquals(['laravel', 'php'], $service->getAvailableTags());
    }
}
