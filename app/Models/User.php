<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public function isAdmin(): bool
    {
        return (bool) $this->is_admin;
    }
}
