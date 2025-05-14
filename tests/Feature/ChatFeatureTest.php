<?php

declare(strict_types=1);

use App\Events\MessageSent;
use App\Models\User;
use Illuminate\Support\Facades\Event;

describe('Chat feature', function (): void {
    it('requires authentication to access chat', function (): void {
        $this->get('/chat')->assertRedirect('/login');
        $this->post('/chat/send', [])->assertRedirect('/login');
    });

    it('shows chat for authenticated user', function (): void {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/chat')->assertOk();
    });

    it('validates message sending', function (): void {
        $user = User::factory()->create();
        $recipient = User::factory()->create();
        $this->actingAs($user)
            ->postJson('/chat/send', [])->assertStatus(422);
        $this->actingAs($user)
            ->postJson('/chat/send', [
                'recipient_id' => $recipient->id,
                'message' => '',
            ])->assertStatus(422);
    });

    it('sends a message and broadcasts event', function (): void {
        $this->withoutExceptionHandling();
        Event::fake([MessageSent::class]);
        $user = User::factory()->create();
        $recipient = User::factory()->create();
        $this->actingAs($user)
            ->postJson('/chat/send', [
                'recipient_id' => $recipient->id,
                'message' => 'Hello!',
            ])->assertRedirectToRoute('chat.index');
        $this->assertDatabaseHas('chat_messages', [
            'user_id' => $user->id,
            'recipient_id' => $recipient->id,
            'message' => 'Hello!',
        ]);
        Event::assertDispatched(MessageSent::class);
    });
});
