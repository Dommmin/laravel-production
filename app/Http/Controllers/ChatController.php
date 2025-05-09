<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Http\Requests\SendMessageRequest;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\ResponseFactory;

class ChatController extends Controller
{
    public function index(Request $request): Response|ResponseFactory
    {
        $users = User::where('id', '!=', auth()->id())->orderBy('name')->get(['id', 'name', 'email']);
        return inertia('chat/index', [
            'users' => $users,
            'currentUserId' => auth()->id(),
        ]);
    }

    public function show(Request $request, $chatUuid): Response|ResponseFactory
    {
        $chat = Chat::with(['users', 'messages.user', 'messages.readBy'])->where('id', $chatUuid)->firstOrFail();
        $messagesQuery = $chat->messages()->latest()->paginate(20);
        $pagination = $messagesQuery->toArray();
        unset($pagination['data']);
        $messages = collect($messagesQuery->items())
            ->sortBy('created_at')
            ->values()
            ->map(function (ChatMessage $msg) {
                $msg->withRelationshipAutoloading();
                return [
                    'id' => $msg->id,
                    'user_id' => $msg->user_id,
                    'message' => $msg->message,
                    'created_at' => $msg->created_at?->toISOString(),
                    'updated_at' => $msg->updated_at?->toISOString(),
                    'read_by' => $msg->readBy->pluck('id'),
                    'user' => [
                        'id' => $msg->user->id,
                        'name' => $msg->user->name,
                        'avatar' => $msg->user->avatar ?? null,
                    ],
                ];
            });
        return inertia('chat/show', [
            'chat' => $chat,
            'users' => $chat->users,
            'currentUserId' => auth()->id(),
            'messages' => $messages,
            'messagesPagination' => $pagination,
        ]);
    }

    public function store(Request $request, $chatUuid)
    {
        $chat = Chat::where('id', $chatUuid)->firstOrFail();
        $data = $request->validate([
            'message' => 'required|string',
        ]);

        $message = $chat->messages()->create([
            'user_id' => auth()->id(),
            'message' => $data['message'],
        ]);

        broadcast(new \App\Events\MessageSent($message))->toOthers();

        return to_route('chat.show', $chat->id)->with('success', 'Message sent successfully.');
    }

    public function findOrCreate(Request $request, $userId)
    {
        $user = \App\Models\User::findOrFail($userId);
        $currentUser = auth()->user();
        // Szukamy czatu, w którym są tylko ci dwaj użytkownicy
        $chat = Chat::whereHas('users', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->whereHas('users', function ($q) use ($currentUser) {
            $q->where('user_id', $currentUser->id);
        })
        ->withCount('users')
        ->get()
        ->first(function ($chat) {
            return $chat->users_count === 2;
        });
        if (!$chat) {
            $chat = Chat::create();
            $chat->users()->attach([$user->id, $currentUser->id]);
        }
        return redirect()->route('chat.show', $chat->id);
    }
}
