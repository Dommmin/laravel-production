<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Tag;

/**
 * Service for retrieving available tags from the database
 */
class TagService
{
    public function getAvailableTags(): array
    {
        return Tag::query()->pluck('name')->all();
    }
}
