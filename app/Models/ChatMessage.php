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
        'user_id',
        'recipient_id',
        'message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public static function getChatMessages(?int $recipientId, ?int $userId = null, int $perPage = 15)
    {
        if (! $recipientId || ! $userId) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        return self::query()
            ->where(function ($query) use ($userId, $recipientId) {
                $query->where('user_id', $userId)
                    ->where('recipient_id', $recipientId);
            })
            ->orWhere(function ($query) use ($userId, $recipientId) {
                $query->where('user_id', $recipientId)
                    ->where('recipient_id', $userId);
            })
            ->latest('created_at')
            ->paginate($perPage);
    }
}
