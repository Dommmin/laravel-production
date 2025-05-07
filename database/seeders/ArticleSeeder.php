<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\User;
use App\Models\Tag;

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
            ['lat' => 52.2297, 'lon' => 21.0122, 'city_name' => 'Warszawa'],
            ['lat' => 50.0647, 'lon' => 19.9450, 'city_name' => 'Kraków'],
            ['lat' => 51.1079, 'lon' => 17.0385, 'city_name' => 'Wrocław'],
        ];
        foreach (range(1, 1000) as $i) {
            $user = $users->random();
            $loc = $locations[array_rand($locations)];
            $article = Article::create([
                'title' => "Przykładowy artykuł $i",
                'content' => "To jest treść artykułu $i o technologii.",
                'user_id' => $user->id,
                'location' => ['lat' => $loc['lat'], 'lon' => $loc['lon']],
                'city_name' => $loc['city_name'],
            ]);
            $article->tags()->attach($tags->random(rand(1, 3)));
        }
    }
}
