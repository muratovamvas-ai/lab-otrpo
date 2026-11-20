<?php

namespace App\Http\Controllers;

use App\Models\MessierObject;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    /**
     * Лента: новые объекты друзей в хронологическом порядке
     * (по дате создания, сначала самые свежие).
     */
    public function index(Request $request): View
    {
        $friends = $request->user()->friends()->orderBy('username')->get();

        $objects = MessierObject::with('user')
            ->withCount('comments')
            ->whereIn('user_id', $friends->pluck('id'))
            ->latest() // created_at по убыванию
            ->get();

        return view('feed.index', compact('objects', 'friends'));
    }
}
