<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FriendshipController extends Controller
{
    /**
     * Добавить пользователя в друзья.
     * Обратная связь (он -> вы) создаётся автоматически событием модели Friendship.
     */
    public function store(Request $request, User $user): RedirectResponse
    {
        $me = $request->user();

        if ($user->is($me)) {
            return back()->with('error', 'Нельзя добавить в друзья самого себя.');
        }

        Friendship::firstOrCreate([
            'user_id' => $me->id,
            'friend_id' => $user->id,
        ]);

        return back()->with('status', "Пользователь {$user->username} теперь ваш друг (дружба обоюдная).");
    }

    /** Убрать из друзей. Событие модели удалит и обратную связь. */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        Friendship::where('user_id', $request->user()->id)
            ->where('friend_id', $user->id)
            ->first()
            ?->delete();

        return back()->with('status', "Пользователь {$user->username} больше не в ваших друзьях.");
    }
}
