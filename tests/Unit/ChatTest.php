<?php

use App\Models\ChatMessage;
use App\Models\User;
use Tests\TestCase;

uses(TestCase::class);

describe('ChatMessage model', function () {
    it('has fillable fields', function () {
        $model = new ChatMessage;
        expect($model->getFillable())->toBe(['user_id', 'recipient_id', 'message']);
    });

    it('can create a chat message', function () {
        $user = User::factory()->create();
        $recipient = User::factory()->create();
        $msg = ChatMessage::create([
            'user_id' => $user->id,
            'recipient_id' => $recipient->id,
            'message' => 'Hello!',
        ]);

        expect($msg->user_id)->toBe($user->id)
            ->and($msg->recipient_id)->toBe($recipient->id)
            ->and($msg->message)->toBe('Hello!');
    });

    it('has user and recipient relations', function () {
        $user = User::factory()->create();
        $recipient = User::factory()->create();
        $msg = ChatMessage::create([
            'user_id' => $user->id,
            'recipient_id' => $recipient->id,
            'message' => 'Test',
        ]);

        expect($msg->user->id)->toBe($user->id)
            ->and($msg->recipient->id)->toBe($recipient->id);
    });
});
