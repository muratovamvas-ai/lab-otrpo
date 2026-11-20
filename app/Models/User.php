<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Поля, доступные для массового заполнения.
     * is_admin здесь намеренно нет: админом нельзя стать через форму,
     * флаг ставится только вручную (tinker) или сидером.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function booted(): void
    {
        // Админ может быть только один
        static::saving(function (User $user) {
            if ($user->is_admin && $user->isDirty('is_admin')) {
                $otherAdminExists = static::where('is_admin', true)
                    ->where('id', '!=', $user->id ?? 0)
                    ->exists();

                if ($otherAdminExists) {
                    throw new \DomainException('На сайте может быть только один администратор.');
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /** username всегда хранится в нижнем регистре без пробелов по краям. */
    protected function username(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value === null ? null : mb_strtolower(trim($value)),
        );
    }

    /**
     * Маршруты вида /users/{user}/objects ищут пользователя по username, а не по id.
     */
    public function getRouteKeyName(): string
    {
        return 'username';
    }

    /** Объекты, добавленные пользователем. */
    public function messierObjects(): HasMany
    {
        return $this->hasMany(MessierObject::class);
    }

    /** Комментарии, которые оставил пользователь. */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Друзья пользователя (связь многие-ко-многим users <-> users через таблицу friendships).
     * Дружба обоюдная, поэтому достаточно одной стороны связи.
     */
    public function friends(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'friendships', 'user_id', 'friend_id')
            ->withTimestamps();
    }

    /** Друг ли этот пользователь с $user. */
    public function isFriendWith(User $user): bool
    {
        return $this->friends()->where('users.id', $user->id)->exists();
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }
}
