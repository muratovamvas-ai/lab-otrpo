<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessierObjectRequest;
use App\Models\MessierObject;
use App\Models\User;
use App\Services\ImageResizer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Все маршруты контроллера закрыты middleware «auth» (см. routes/web.php).
 * Права на отдельные действия проверяются здесь через Gates, в шаблонах — через @can,
 * а на уровне модели — через события (см. MessierObject::booted()).
 */
class MessierObjectController extends Controller
{
    public function __construct(private ImageResizer $images)
    {
    }

    /** Список объектов всех пользователей (администратор видит и удалённые). */
    public function index(): View
    {
        $query = MessierObject::with('user')->withCount('comments')->orderBy('id');

        if (Gate::allows('view-trashed')) {
            $query->withTrashed();
        }

        return view('objects.index', [
            'objects' => $query->get(),
            'heading' => 'Все объекты',
            'owner' => null,
            'users' => collect(),
            'isFriend' => false,
        ]);
    }

    /** Список объектов одного пользователя: /users/{username}/objects */
    public function byUser(Request $request, User $user): View
    {
        $query = $user->messierObjects()->with('user')->withCount('comments')->orderBy('id');

        // полный список, включая удалённые, видит только администратор
        if (Gate::allows('view-trashed')) {
            $query->withTrashed();
        }

        return view('objects.index', [
            'objects' => $query->get(),
            'heading' => 'Объекты пользователя '.$user->username,
            'owner' => $user,
            'users' => User::orderBy('username')->get(['id', 'name', 'username']),
            // дружите ли вы с владельцем списка (для кнопки «в друзья»)
            'isFriend' => $user->isNot($request->user())
                && $request->user()->isFriendWith($user),
        ]);
    }

    /** AJAX: проверка, занято ли обозначение (для проверки «на лету» в форме). */
    public function checkName(Request $request): JsonResponse
    {
        // тот же вид, в котором обозначение хранится в БД: «m 10» → «M10»
        $name = mb_strtoupper(preg_replace('/\s+/u', '', (string) $request->query('name', '')));

        $taken = $name !== '' && MessierObject::where('name', $name)
            ->when($request->query('ignore'), fn ($query, $id) => $query->where('id', '!=', $id))
            ->exists();

        return response()->json(['taken' => $taken]);
    }

    /** Форма добавления: доступна любому авторизованному пользователю. */
    public function create(): View
    {
        return view('objects.create', ['object' => new MessierObject()]);
    }

    /** Сохранение нового объекта: в БД записывается id автора. */
    public function store(MessierObjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->images->storeUploaded($request->file('image'));
        }

        // user_id берём у текущего пользователя, а не из формы
        $data['user_id'] = $request->user()->id;

        $object = MessierObject::create($data);

        return redirect()
            ->route('objects.index')
            ->with('status', "Объект «{$object->full_title}» добавлен.");
    }

    /**
     * Подробности об объекте.
     * Обычный запрос — полная страница, AJAX-запрос из Modal — только содержимое.
     */
    public function show(Request $request, MessierObject $object): View
    {
        // удалённый объект видит только администратор
        if ($object->trashed()) {
            Gate::authorize('view-trashed');
        }

        if ($request->ajax()) {
            $object->load('user');

            return view('objects._details', compact('object'));
        }

        // комментарии вместе с авторами; id друзей нужны для подсветки их комментариев
        $object->load([
            'user',
            'comments' => fn ($query) => $query->oldest()->with('user'), // старые сверху, новые снизу
        ]);
        $friendIds = $request->user()->friends()->pluck('users.id')->all();

        return view('objects.show', compact('object', 'friendIds'));
    }

    /** Форма редактирования: только автор или администратор. */
    public function edit(MessierObject $object): View
    {
        Gate::authorize('update-object', $object);

        return view('objects.edit', compact('object'));
    }

    /** Обновление объекта: только автор или администратор. */
    public function update(MessierObjectRequest $request, MessierObject $object): RedirectResponse
    {
        Gate::authorize('update-object', $object);

        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            $this->images->delete($object->image_path); // старую картинку убираем
            $data['image_path'] = $this->images->storeUploaded($request->file('image'));
        }

        $object->update($data);

        return redirect()
            ->route('objects.show', $object)
            ->with('status', 'Изменения сохранены.');
    }

    /** Мягкое удаление: автор или администратор. Запись остаётся в БД с deleted_at. */
    public function destroy(MessierObject $object): RedirectResponse
    {
        Gate::authorize('delete-object', $object);

        $object->delete();

        return redirect()
            ->route('objects.index')
            ->with('status', "Объект «{$object->full_title}» удалён.");
    }

    /** Восстановление мягко удалённого объекта: только администратор. */
    public function restore(MessierObject $object): RedirectResponse
    {
        Gate::authorize('restore-object', $object);

        $object->restore();

        return back()->with('status', "Объект «{$object->full_title}» восстановлен.");
    }

    /** Полное удаление без возврата: только администратор. */
    public function forceDelete(MessierObject $object): RedirectResponse
    {
        Gate::authorize('force-delete-object', $object);

        $imagePath = $object->image_path;
        $title = $object->full_title;

        $object->forceDelete();
        $this->images->delete($imagePath); // файл картинки удаляем только после удаления записи

        return redirect()
            ->route('objects.index')
            ->with('status', "Объект «{$title}» удалён навсегда.");
    }
}
