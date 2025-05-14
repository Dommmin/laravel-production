<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChatMessageFactory extends Factory
{
    public function definition(): array
    {
        $userId = User::inRandomOrder()->first()->id;
        $recipientId = User::inRandomOrder()->where('id', '!=', $userId)->first()->id;

        return [
            'user_id' => $userId,
            'recipient_id' => $recipientId,
            'message' => fake()->realTextBetween(10, 100),
        ];
    }
}
