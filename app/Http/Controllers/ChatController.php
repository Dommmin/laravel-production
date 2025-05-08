<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Requests\SendMessageRequest;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $recipientId = $request->input('recipient_id');
        $users = User::getChatUsers();
        $recipient = $recipientId ? User::getRecipient($recipientId) : $users->first();
        $messages = ChatMessage::getChatMessages($recipient?->id);

        return inertia('chat/index', [
            'users' => $users,
            'currentUserId' => auth()->id(),
            'recipient' => $recipient,
            'messages' => inertia()->merge(fn () => $messages->items()),
            'messagesPagination' => $messages->toArray(),
        ]);
    }

    public function store(SendMessageRequest $request)
    {
        $data = $request->validated();

        $message = ChatMessage::create([
            'user_id' => auth()->id(),
            'recipient_id' => $data['recipient_id'],
            'message' => $data['message'],
        ]);

        broadcast(new MessageSent($message))->toOthers();

        return back()->with('success', 'Message sent successfully.');
    }
}
