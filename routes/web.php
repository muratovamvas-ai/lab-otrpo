<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\MessierObjectController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Маршруты аутентификации (/login, /register, /logout) добавляет Laravel Fortify.

Route::redirect('/', '/objects');

// Всё приложение доступно только после входа
Route::middleware('auth')->group(function () {

    // Список пользователей и объекты конкретного пользователя (по username)
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}/objects', [MessierObjectController::class, 'byUser'])->name('users.objects');

    // Дружба: добавить в друзья / убрать из друзей (связь обоюдная)
    Route::post('users/{user}/friend', [FriendshipController::class, 'store'])->name('friends.store');
    Route::delete('users/{user}/friend', [FriendshipController::class, 'destroy'])->name('friends.destroy');

    // Лента новых объектов друзей
    Route::get('feed', [FeedController::class, 'index'])->name('feed');

    // Комментарии: добавить к объекту, удалить свой
    Route::post('objects/{object}/comments', [CommentController::class, 'store'])->name('comments.store');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // AJAX-проверка обозначения (должен идти ДО resource, иначе «check-name» примется за {object})
    Route::get('objects/check-name', [MessierObjectController::class, 'checkName'])->name('objects.check-name');

    // Администратор: восстановление и полное удаление. withTrashed() — искать и среди мягко удалённых
    Route::patch('objects/{object}/restore', [MessierObjectController::class, 'restore'])
        ->withTrashed()->name('objects.restore');
    Route::delete('objects/{object}/force', [MessierObjectController::class, 'forceDelete'])
        ->withTrashed()->name('objects.force-delete');

    // index, create, store, show, edit, update, destroy
    // (show может открыть и удалённый объект — доступ к нему проверяется в контроллере)
    Route::resource('objects', MessierObjectController::class)
        ->parameters(['objects' => 'object'])
        ->withTrashed(['show']);
});
