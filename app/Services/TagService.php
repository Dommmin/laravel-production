<?php

namespace App\Services;

use App\Models\Tag;

class TagService
{
    public function getAvailableTags(): array
    {
        return Tag::query()->pluck('name')->all();
    }
} 