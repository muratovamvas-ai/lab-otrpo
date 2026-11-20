<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /** Список всех пользователей со ссылками на их объекты и кнопками дружбы. */
    public function index(Request $request): View
    {
        $users = User::withCount('messierObjects')->orderBy('username')->get();
        $friendIds = $request->user()->friends()->pluck('users.id')->all();

        return view('users.index', compact('users', 'friendIds'));
    }
}
