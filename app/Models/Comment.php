<?php

namespace App\Models;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

/**
 * Комментарий к объекту каталога. Таблица: comments.
 */
class Comment extends Model
{
    protected $fillable = [
        'messier_object_id',
        'user_id',
        'body',
    ];

    // ---------- События модели ----------

    protected static function booted(): void
    {
        // Автором становится текущий пользователь
        static::creating(function (Comment $comment) {
            if (! $comment->user_id && Auth::check()) {
                $comment->user_id = Auth::id();
            }

            if (! app()->runningInConsole() && ! Auth::check()) {
                throw new AuthorizationException('Комментировать могут только авторизованные пользователи.');
            }
        });

        // Удалить комментарий может автор или администратор
        static::deleting(function (Comment $comment) {
            if (! app()->runningInConsole() && Gate::denies('delete-comment', $comment)) {
                throw new AuthorizationException('Недостаточно прав для удаления комментария.');
            }
        });
    }

    // ---------- Связи ----------

    /** Автор комментария. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Объект, к которому оставлен комментарий. */
    public function messierObject(): BelongsTo
    {
        return $this->belongsTo(MessierObject::class);
    }
}
