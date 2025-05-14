<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        $cities = [
            ['name' => 'London', 'lat' => 52.2297, 'lon' => 21.0122],
            ['name' => 'New York', 'lat' => 50.0647, 'lon' => 19.9450],
            ['name' => 'Berlin', 'lat' => 51.1079, 'lon' => 17.0385],
        ];
        $city = fake()->randomElement($cities);

        return [
            'title' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'user_id' => User::factory(),
            'location' => ['lat' => $city['lat'], 'lon' => $city['lon']],
            'city_name' => $city['name'],
        ];
    }
}
