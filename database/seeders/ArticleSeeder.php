<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $tags = Tag::all();

        $locations = [
            ['lat' => 52.2297, 'lon' => 21.0122, 'city_name' => 'London'],
            ['lat' => 50.0647, 'lon' => 19.9450, 'city_name' => 'New York'],
            ['lat' => 51.1079, 'lon' => 17.0385, 'city_name' => 'Berlin'],
        ];

        for ($i = 0; $i < 1000; $i++) {
            $user = $users->random();
            $loc = $locations[array_rand($locations)];
            $article = Article::create([
                'title' => fake()->paragraph(1),
                'content' => fake()->realTextBetween(100, 200),
                'user_id' => $user->id,
                'location' => ['lat' => $loc['lat'], 'lon' => $loc['lon']],
                'city_name' => $loc['city_name'],
            ]);
            $article->tags()->attach($tags->random(rand(1, 3)));
        }
    }
}
