<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Pagination\LengthAwarePaginator;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'chat_id',
        'user_id',
        'message',
        'read_at',
    ];

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipient(): BelongsTo
    {
        // Usuwamy recipient, niepotrzebne
    }

    public static function getChatMessages(?int $recipientId, ?int $userId = null, int $perPage = 15)
    {
        // Usuwamy, niepotrzebne
    }

    public function readBy()
    {
        return $this->belongsToMany(User::class, 'chat_message_reads')->withTimestamps();
    }
}
