<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => bcrypt('Pa$$w0rd!')]);
        User::factory()->create(['name' => 'Jan Kowalski', 'email' => 'jan@example.com']);
        User::factory()->create(['name' => 'Anna Nowak', 'email' => 'anna@example.com']);
        User::factory()->create(['name' => 'Piotr Zielinski', 'email' => 'piotr@example.com']);
    }
}
