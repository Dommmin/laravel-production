<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Tag;
use App\Services\TagService;
use Mockery;
use PHPUnit\Framework\TestCase;

final class TagServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_available_tags_returns_tags(): void
    {
        $mock = Mockery::mock('alias:'.Tag::class);
        $mock->shouldReceive('query->pluck->all')->andReturn(['laravel', 'php']);
        $tagService = new TagService;
        $this->assertEquals(['laravel', 'php'], $tagService->getAvailableTags());
    }
}
