<?php

namespace App\Services;

use App\Models\Article;

/**
 * Serwis do pobierania dostępnych miast z artykułów
 */
class CityService
{
    public function getAvailableCities(): array
    {
        return Article::query()->distinct()->pluck('city_name')->all();
    }
}
