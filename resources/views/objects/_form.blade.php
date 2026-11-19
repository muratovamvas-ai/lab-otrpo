{{--
  Одна форма для создания и редактирования.
  Валидация: HTML5 (required, maxlength, pattern, type=date) + Bootstrap (was-validated, is-invalid)
  и на сервере (App\Http\Requests\MessierObjectRequest) — ошибки выводятся под полями.
--}}
<form action="{{ $action }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
  @csrf
  @if ($method !== 'POST')
    @method($method)
  @endif

  <div class="row g-3">
    <div class="col-md-6">
      <label for="constellation" class="form-label">Созвездие</label>
      <input type="text" id="constellation" name="constellation" maxlength="100" required
             pattern="[А-Яа-яЁё0-9\s\-]+"
             class="form-control @error('constellation') is-invalid @enderror"
             value="{{ old('constellation', $object->constellation) }}" placeholder="Например: Змееносец">
      <div class="invalid-feedback">@error('constellation') {{ $message }} @else Только русские буквы и цифры. @enderror</div>
    </div>

    <div class="col-md-3">
      <label for="name" class="form-label">Обозначение</label>
      <input type="text" id="name" name="name" maxlength="20" required
             data-check-url="{{ route('objects.check-name') }}"
             data-ignore-id="{{ $object->id }}"
             data-empty-message="Введите обозначение, например M10."
             class="form-control @error('name') is-invalid @enderror"
             value="{{ old('name', $object->name) }}" placeholder="M10">
      <div class="invalid-feedback">@error('name') {{ $message }} @else Введите обозначение, например M10. @enderror</div>
    </div>

    <div class="col-md-3">
      <label for="catalogued_at" class="form-label">Дата внесения в каталог</label>
      <input type="date" id="catalogued_at" name="catalogued_at" max="{{ now()->toDateString() }}"
             class="form-control @error('catalogued_at') is-invalid @enderror"
             value="{{ old('catalogued_at', $object->catalogued_at?->format('Y-m-d')) }}">
      <div class="invalid-feedback">@error('catalogued_at') {{ $message }} @else Дата не может быть в будущем. @enderror</div>
    </div>

    <div class="col-12">
      @php
        $types = \App\Models\MessierObject::TYPES;
        $currentType = old('type', $object->type);
        // если у объекта в БД тип, которого нет в списке, показываем его тоже
        if ($currentType && ! in_array($currentType, $types, true)) {
            array_unshift($types, $currentType);
        }
      @endphp
      <label for="type" class="form-label">Тип объекта</label>
      <select id="type" name="type" required
              class="form-select @error('type') is-invalid @enderror">
        <option value="" disabled @selected(! $currentType)>Выберите тип объекта…</option>
        @foreach ($types as $type)
          <option value="{{ $type }}" @selected($currentType === $type)>{{ $type }}</option>
        @endforeach
      </select>
      <div class="invalid-feedback">@error('type') {{ $message }} @else Выберите тип объекта из списка. @enderror</div>
    </div>

    <div class="col-12">
      <label for="short_description" class="form-label">Краткое описание (для карточки)</label>
      <textarea id="short_description" name="short_description" rows="2" maxlength="500" required
                data-min-words="{{ \App\Models\MessierObject::DESCRIPTION_MIN_WORDS }}"
                data-max-words="{{ \App\Models\MessierObject::DESCRIPTION_MAX_WORDS }}"
                data-empty-message="Введите краткое описание."
                class="form-control @error('short_description') is-invalid @enderror">{{ old('short_description', $object->short_description) }}</textarea>
      <div class="form-text word-counter"></div>
      <div class="invalid-feedback">@error('short_description') {{ $message }} @else Введите краткое описание. @enderror</div>
    </div>

    <div class="col-12">
      <label for="details" class="form-label">Подробное описание (для окна «Подробнее»)</label>
      <textarea id="details" name="details" rows="7" maxlength="5000" required
                data-min-words="{{ \App\Models\MessierObject::DETAILS_MIN_WORDS }}"
                data-max-words="{{ \App\Models\MessierObject::DETAILS_MAX_WORDS }}"
                data-empty-message="Введите подробное описание."
                class="form-control @error('details') is-invalid @enderror">{{ old('details', $object->details) }}</textarea>
      <div class="form-text">
        Абзацы разделяйте пустой строкой. Подсказку к слову задают так: <code>[шаровое скопление|Плотный шар из старых звёзд]</code>.
      </div>
      <div class="form-text word-counter"></div>
      <div class="invalid-feedback">@error('details') {{ $message }} @else Введите подробное описание. @enderror</div>
    </div>

    <div class="col-12">
      <label for="image" class="form-label">Изображение (JPG, PNG, WEBP до 5 МБ)</label>
      <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
             class="form-control @error('image') is-invalid @enderror"
             @if (!$object->exists) required @endif>
      <div class="invalid-feedback">@error('image') {{ $message }} @else Выберите изображение. @enderror</div>
      <div class="form-text">Картинка будет автоматически обрезана и уменьшена до 960×600 (как в карточке).</div>

      <img id="imagePreview" class="image-preview mt-2" src="{{ $object->image_url }}" alt="Предпросмотр">
    </div>
  </div>

  <div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary">
      <i class="fa-solid fa-floppy-disk"></i> {{ $submitText }}
    </button>
    <a href="{{ $object->exists ? route('objects.show', $object) : route('objects.index') }}" class="btn btn-outline-secondary">Отмена</a>
  </div>
</form>
