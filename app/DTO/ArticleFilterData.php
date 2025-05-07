<?php

namespace App\DTO;

use Spatie\LaravelData\Data;

class ArticleFilterData extends Data
{
    public function __construct(
        public ?string $q = '',
        public ?string $tag = null,
        public ?string $city = null,
        public ?int $radius = null,
        public ?float $lat = null,
        public ?float $lon = null,
        public ?int $page = 1,
        public ?int $size = 20,
    ) {}
}
