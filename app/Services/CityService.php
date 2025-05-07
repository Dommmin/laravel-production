<?php

namespace App\Services;

use App\Models\Article;

class CityService
{
    public function getAvailableCities(): array
    {
        return Article::query()->distinct()->pluck('city_name')->all();
    }
} 