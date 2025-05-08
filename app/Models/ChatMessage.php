<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    protected $fillable = [
        'user_id',
        'recipient_id',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public static function getChatMessages(?int $recipientId): Paginator
    {
        return self::query()
            ->where('user_id', auth()->id())
            ->where('recipient_id', $recipientId)
            ->orderBy('created_at')
            ->simplePaginate();
    }
}
