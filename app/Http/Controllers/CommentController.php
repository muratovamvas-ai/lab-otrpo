<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\MessierObject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Добавить комментарий к объекту. Комментировать может любой авторизованный
     * пользователь — и свои объекты, и чужие. Автором записывается текущий пользователь.
     */
    public function store(Request $request, MessierObject $object): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:1000'],
        ], [
            'required' => 'Напишите текст комментария.',
            'min' => 'Комментарий слишком короткий (минимум :min символа).',
            'max' => 'Комментарий не должен быть длиннее :max символов.',
        ]);

        $object->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return redirect(route('objects.show', $object).'#comments')
            ->with('status', 'Комментарий добавлен.');
    }

    /** Удалить комментарий: автор или администратор. */
    public function destroy(Comment $comment): RedirectResponse
    {
        Gate::authorize('delete-comment', $comment);

        $object = $comment->messierObject()->withTrashed()->first();
        $comment->delete();

        return redirect(route('objects.show', $object).'#comments')
            ->with('status', 'Комментарий удалён.');
    }
}
