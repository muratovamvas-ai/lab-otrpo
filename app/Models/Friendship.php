<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Дружба: строка user_id -> friend_id. Таблица: friendships.
 *
 * Дружба обоюдная: события модели автоматически создают и удаляют
 * обратную строку friend_id -> user_id.
 */
class Friendship extends Model
{
    protected $fillable = [
        'user_id',
        'friend_id',
    ];

    protected static function booted(): void
    {
        // Событие «обоюдной дружбы»: как только A добавил B в друзья,
        // автоматически создаётся обратная связь B -> A.
        static::created(function (Friendship $friendship) {
            // firstOrCreate не создаст дубль, поэтому цепочка событий на этом заканчивается
            Friendship::firstOrCreate([
                'user_id' => $friendship->friend_id,
                'friend_id' => $friendship->user_id,
            ]);
        });

        // Если A убрал B из друзей, обратная связь B -> A удаляется тоже.
        // Запрос delete() выполняется без событий модели, поэтому зацикливания нет.
        static::deleted(function (Friendship $friendship) {
            Friendship::where('user_id', $friendship->friend_id)
                ->where('friend_id', $friendship->user_id)
                ->delete();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function friend(): BelongsTo
    {
        return $this->belongsTo(User::class, 'friend_id');
    }
}
