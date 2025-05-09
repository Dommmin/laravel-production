<?php

namespace App\Services;

use App\Models\Article;

/**
 * Service for retrieving available cities from articles
 */
class CityService
{
    public function getAvailableCities(): array
    {
        return Article::query()->distinct()->pluck('city_name')->all();
    }
}
