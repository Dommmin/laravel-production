<?php

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
            'message' => $this->faker->realTextBetween(10, 100),
        ];
    }
}
