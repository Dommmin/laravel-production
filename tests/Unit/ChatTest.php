<?php

declare(strict_types=1);

use App\Models\ChatMessage;
use App\Models\User;
use App\Models\Chat;
use Tests\TestCase;

uses(TestCase::class);

describe('ChatMessage model', function (): void {
    it('has fillable fields', function (): void {
        $model = new ChatMessage;
        expect($model->getFillable())->toBe(['chat_id', 'user_id', 'recipient_id', 'message', 'read_at']);
    });

    it('can create a chat message', function (): void {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $msg = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'message' => 'Hello!',
        ]);

        expect($msg->chat_id)->toBe($chat->id)
            ->and($msg->user_id)->toBe($user->id)
            ->and($msg->message)->toBe('Hello!');
    });

    it('has chat and user relations', function (): void {
        $user = User::factory()->create();
        $chat = Chat::factory()->create();
        $msg = ChatMessage::create([
            'chat_id' => $chat->id,
            'user_id' => $user->id,
            'message' => 'Test',
        ]);

        expect($msg->chat->id)->toBe($chat->id)
            ->and($msg->user->id)->toBe($user->id);
    });
});
