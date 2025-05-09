<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Requests\SendMessageRequest;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;

class ChatController extends Controller
{
    public function index(Request $request): Response|ResponseFactory
    {
        $recipientId = $request->input('recipient_id');
        $page = $request->integer('page', 1);

        $users = User::getChatUsers();
        $recipient = $recipientId ? User::getRecipient($recipientId) : $users->first();
        $messagesQuery = ChatMessage::getChatMessages($recipient->id ?? null, auth()->id());

        $pagination = $messagesQuery->toArray();
        unset($pagination['data']);

        $messages = collect($messagesQuery->items())
            ->sortBy('created_at')
            ->values()
            ->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'user_id' => $msg->user_id,
                    'recipient_id' => $msg->recipient_id,
                    'message' => $msg->message,
                    'created_at' => $msg->created_at?->toISOString(),
                    'updated_at' => $msg->updated_at?->toISOString(),
                ];
            });

        return inertia('chat/index', [
            'users' => $users,
            'currentUserId' => auth()->id(),
            'recipient' => $recipient,
            'messages' => $messages,
            'messagesPagination' => $pagination,
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
