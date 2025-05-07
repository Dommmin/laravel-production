<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create(['name' => 'Jan Kowalski', 'email' => 'jan@example.com']);
        User::factory()->create(['name' => 'Anna Nowak', 'email' => 'anna@example.com']);
        User::factory()->create(['name' => 'Piotr Zielinski', 'email' => 'piotr@example.com']);
    }
}
