<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\MessierObject;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    /**
     * Правила доступа (Gates). Используются в контроллере (Gate::authorize),
     * в шаблонах (@can) и в событиях модели MessierObject.
     */
    public function boot(): void
    {
        // Редактировать может автор объекта или администратор
        Gate::define('update-object', function (User $user, MessierObject $object) {
            return $user->isAdmin() || (int) $object->user_id === (int) $user->id;
        });

        // Мягко удалить может автор объекта или администратор
        Gate::define('delete-object', function (User $user, MessierObject $object) {
            return $user->isAdmin() || (int) $object->user_id === (int) $user->id;
        });

        // Восстановить мягко удалённый объект — только администратор
        Gate::define('restore-object', function (User $user, ?MessierObject $object = null) {
            return $user->isAdmin();
        });

        // Удалить без возврата - только администратор
        Gate::define('force-delete-object', function (User $user, ?MessierObject $object = null) {
            return $user->isAdmin();
        });

        // Удалить комментарий может его автор или администратор
        Gate::define('delete-comment', function (User $user, Comment $comment) {
            return $user->isAdmin() || (int) $comment->user_id === (int) $user->id;
        });

        // Видеть удалённые объекты - только администратор
        Gate::define('view-trashed', function (User $user) {
            return $user->isAdmin();
        });
    }
}
