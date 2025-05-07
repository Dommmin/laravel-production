<?php

namespace App\Services;

use App\Models\Tag;

/**
 * Serwis do pobierania dostępnych tagów z bazy
 */
class TagService
{
    public function getAvailableTags(): array
    {
        return Tag::query()->pluck('name')->all();
    }
}
