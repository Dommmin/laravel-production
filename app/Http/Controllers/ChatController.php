<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Requests\StoreMessageRequest;
use App\Models\Chat;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;

class ChatController extends Controller
{
    public function index(Request $request): Response|ResponseFactory
    {
        return inertia('chat/index', [
            'chats' => Chat::getList(),
            'currentUserId' => auth()->id(),
        ]);
    }

    public function show(Request $request, Chat $chat): Response
    {
        return inertia('chat/show', [
            'chat' => $chat->load(['users', 'messages.user']),
            'chats' => Chat::getList(),
            'currentUserId' => auth()->id(),
            'messages' =>  $chat->messages,
        ]);
    }

    public function store(StoreMessageRequest $storeMessageRequest, Chat $chat)
    {
        $model = $chat->messages()->create($storeMessageRequest->validated());

        broadcast(new MessageSent($model))->toOthers();

        return to_route('chat.show', $chat->id)->with('success', 'Message sent successfully.');
    }
}
