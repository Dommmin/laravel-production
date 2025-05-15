<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;
use Faker\Factory as Faker;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

final class ChatTest extends DuskTestCase
{
    public function test_user_can_send_and_see_message_in_chat(): void
    {
        $this->browse(function (Browser $browser): void {
            $generator = Faker::create();
            $sender = User::factory()->create(['password' => 'password']);
            $recipient = User::factory()->create();
            $message = $generator->sentence;

            $browser->loginAs($sender)
                ->visit('/chat?recipient_id='.$recipient->id)
                ->screenshot('chat-page-after-visit')
                ->storeSource('chat-page-after-visit.html')
                ->waitForText($recipient->name, 10)
                ->assertSee($recipient->name)
                ->type('input[name="message"]', $message)
                ->press('Send')
                ->waitForText($message)
                ->assertSee($message);
        });
    }

    public function test_user_can_switch_conversation_and_see_empty_state(): void
    {
        $this->browse(function (Browser $browser): void {
            $sender = User::factory()->create(['password' => 'password']);
            $recipient1 = User::factory()->create();
            $recipient2 = User::factory()->create();

            $browser->loginAs($sender)
                ->visit('/chat?recipient_id='.$recipient1->id)
                ->screenshot('chat-page-switch-1')
                ->storeSource('chat-page-switch-1.html')
                ->waitForText($recipient1->name, 10)
                ->assertSee($recipient1->name)
                ->visit('/chat?recipient_id='.$recipient2->id)
                ->screenshot('chat-page-switch-2')
                ->storeSource('chat-page-switch-2.html')
                ->waitForText($recipient2->name, 10)
                ->assertSee($recipient2->name)
                ->assertSee('No messages yet. Start a conversation!');
        });
    }
}
