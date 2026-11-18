<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessierObjectRequest;
use App\Models\MessierObject;
use App\Services\ImageResizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class MessierObjectController extends Controller
{
    public function __construct(private ImageResizer $images)
    {
    }

    /** Список объектов (карточки). */
    public function index(): View
    {
        $objects = MessierObject::orderBy('id')->get();

        return view('objects.index', compact('objects'));
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

    /** Форма добавления. */
    public function create(): View
    {
        return view('objects.create', ['object' => new MessierObject()]);
    }

    /** Сохранение нового объекта. */
    public function store(MessierObjectRequest $request): RedirectResponse
    {
        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->images->storeUploaded($request->file('image'));
        }

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
        if ($request->ajax()) {
            return view('objects._details', compact('object'));
        }

        return view('objects.show', compact('object'));
    }

    /** Форма редактирования (та же форма, что и при добавлении). */
    public function edit(MessierObject $object): View
    {
        return view('objects.edit', compact('object'));
    }

    /** Обновление объекта. */
    public function update(MessierObjectRequest $request, MessierObject $object): RedirectResponse
    {
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

    /** Мягкое удаление (Soft Delete): запись остаётся в БД с deleted_at. */
    public function destroy(MessierObject $object): RedirectResponse
    {
        $object->delete();

        return redirect()
            ->route('objects.index')
            ->with('status', "Объект «{$object->full_title}» удалён.");
    }
}
