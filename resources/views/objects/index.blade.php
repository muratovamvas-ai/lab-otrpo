@extends('layouts.app')

@section('title', 'Объекты')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <h1 class="mb-0">Лабораторная 3</h1>
    <a href="{{ route('objects.create') }}" class="btn btn-primary">
      <i class="fa-solid fa-plus"></i> Добавить
    </a>
  </div>

  @if ($objects->isEmpty())
    <div class="alert alert-info">
      В каталоге пока нет объектов. Нажмите «Добавить» или выполните <code>php artisan db:seed</code>.
    </div>
  @endif

  <!-- Сетка Bootstrap: количество карточек в ряду зависит от ширины экрана -->
  <main id="objects" class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xxl-4 row-cols-xxxl-5 g-3 g-xl-4">
    @foreach ($objects as $object)
      <div class="col">
        <div class="card object-card h-100">
          <div class="labeledimg">
            <img src="{{ $object->image_url }}" class="card-img-top img-fluid" alt="{{ $object->constellation }}">
            <span class="label">{{ $object->type }}</span>
          </div>
          <div class="card-body d-flex flex-column">
            <h3 class="card-title h5">{{ $object->constellation }}</h3>
            <p class="card-text small">{{ $object->short_description }}</p>

            <div class="mt-auto d-flex align-items-center gap-2">
              {{-- Обычная ссылка на show; JS открывает её содержимое в Modal --}}
              <a href="{{ route('objects.show', $object) }}"
                 class="btn btn-primary btn-sm btn-details"
                 data-title="{{ $object->full_title }}"
                 data-edit-url="{{ route('objects.edit', $object) }}">Подробнее</a>

              <a href="{{ route('objects.edit', $object) }}" class="btn btn-outline-secondary btn-sm" title="Редактировать">
                <i class="fa-solid fa-pen"></i>
              </a>

              <button type="button" class="btn btn-outline-danger btn-sm btn-delete ms-auto" title="Удалить"
                      data-action="{{ route('objects.destroy', $object) }}"
                      data-name="{{ $object->full_title }}">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </main>

  <p id="about" class="text-center text-muted mt-4 mb-0 small">
    Каталог Мессье — список из 110 туманностей, скоплений и галактик, составленный в XVIII веке.
  </p>
@endsection

@push('modals')
  <!-- Одно модальное окно: содержимое подгружается из действия show (AJAX) -->
  <div class="modal fade" id="objectModal" tabindex="-1" aria-labelledby="objectModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="objectModalTitle"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
        </div>
        <div class="modal-body"></div>
        <div class="modal-footer justify-content-between">
          <small class="text-muted">← → — переключение объектов</small>
          <div class="d-flex flex-wrap gap-2">
            <a id="modalShowLink" href="#" class="btn btn-outline-primary">Открыть страницу</a>
            <a id="modalEditLink" href="#" class="btn btn-outline-secondary"><i class="fa-solid fa-pen"></i> Изменить</a>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  @include('objects._delete_modal')
@endpush
