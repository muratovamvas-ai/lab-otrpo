<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;

class UserController extends Controller
{
    /** Список всех пользователей со ссылками на их объекты. */
    public function index(): View
    {
        $users = User::withCount('messierObjects')->orderBy('username')->get();

        return view('users.index', compact('users'));
    }
}
