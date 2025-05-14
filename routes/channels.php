<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id): bool {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.{recipientId}', function ($user, $recipientId): bool {
    return (int) $user->id === (int) $recipientId;
});
