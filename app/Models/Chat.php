<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Chat extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', // uuid
        'name',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model): void {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'chat_users');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public static function getList()
    {
        return Chat::query()
            ->with(['users' => function ($query): void {
                $query->where('user_id', '!=', auth()->id());
            }, 'messages' => function ($query): void {
                $query->latest()->take(1);
            }])
            ->get()
            ->map(function (Chat $chat): \App\Models\Chat {
                $chat->name = $chat->name ?: $chat->users->map(function (User $user) {
                    return $user->name;
                })->implode(', ');

                return $chat;
            });
    }
}
