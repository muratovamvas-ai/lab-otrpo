@extends('layouts.app')

@section('title', $heading)

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h2 mb-0">{{ $heading }}</h1>
    <a href="{{ route('objects.create') }}" class="btn btn-primary">
      <i class="fa-solid fa-plus"></i> Добавить
    </a>
  </div>

  {{-- Навигация по пользователям (на странице объектов конкретного пользователя) --}}
  @if ($owner)
    <ul class="nav nav-pills flex-wrap gap-1 mb-3">
      @foreach ($users as $u)
        <li class="nav-item">
          <a class="nav-link py-1 {{ $u->is($owner) ? 'active' : '' }}" href="{{ route('users.objects', $u) }}">{{ $u->username }}</a>
        </li>
      @endforeach
    </ul>
    <p class="text-muted small">
      {{ $owner->name }} ·
      <a href="{{ route('users.index') }}">все пользователи</a> ·
      <a href="{{ route('objects.index') }}">все объекты</a>
    </p>
  @endif

  @can('view-trashed')
    <div class="alert alert-warning py-2 small">
      <i class="fa-solid fa-user-shield"></i> Вы администратор: в списке показаны и удалённые объекты
      (полупрозрачные), их можно восстановить или удалить навсегда.
    </div>
  @endcan

  @if ($objects->isEmpty())
    <div class="alert alert-info">
      Здесь пока нет объектов. Нажмите «Добавить» или выполните <code>php artisan db:seed</code>.
    </div>
  @endif

  <!-- Сетка Bootstrap: количество карточек в ряду зависит от ширины экрана -->
  <main id="objects" class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xxl-4 row-cols-xxxl-5 g-3 g-xl-4">
    @foreach ($objects as $object)
      <div class="col">
        <div class="card object-card h-100 {{ $object->trashed() ? 'is-trashed' : '' }}">
          <div class="labeledimg">
            <img src="{{ $object->image_url }}" class="card-img-top img-fluid" alt="{{ $object->constellation }}">
            <span class="label">{{ $object->type }}</span>
            @if ($object->trashed())
              <span class="label label-deleted">Удалён</span>
            @endif
          </div>
          <div class="card-body d-flex flex-column">
            <h3 class="card-title h5">{{ $object->constellation }}</h3>
            <p class="card-text small mb-2">{{ $object->short_description }}</p>
            <p class="small text-muted mb-3">
              <i class="fa-solid fa-user"></i>
              @if ($object->user)
                <a href="{{ route('users.objects', $object->user) }}" class="text-reset">{{ $object->user->username }}</a>
              @else
                автор не указан
              @endif
            </p>

            <div class="mt-auto d-flex align-items-center gap-2">
              {{-- Обычная ссылка на show; JS открывает её содержимое в Modal --}}
              <a href="{{ route('objects.show', $object) }}"
                 class="btn btn-primary btn-sm btn-details"
                 data-title="{{ $object->full_title }}"
                 @can('update-object', $object)
                   @unless ($object->trashed())
                     data-edit-url="{{ route('objects.edit', $object) }}"
                   @endunless
                 @endcan>Подробнее</a>

              @if (! $object->trashed())
                {{-- Кнопки видны только тем, у кого есть права (автор или админ) --}}
                @can('update-object', $object)
                  <a href="{{ route('objects.edit', $object) }}" class="btn btn-outline-secondary btn-sm" title="Редактировать">
                    <i class="fa-solid fa-pen"></i>
                  </a>
                @endcan

                @can('delete-object', $object)
                  <button type="button" class="btn btn-outline-danger btn-sm btn-delete ms-auto" title="Удалить"
                          data-action="{{ route('objects.destroy', $object) }}"
                          data-name="{{ $object->full_title }}">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                @endcan
              @else
                {{-- Удалённый объект: только администратор --}}
                @can('restore-object', $object)
                  <form action="{{ route('objects.restore', $object) }}" method="POST" class="d-inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-success btn-sm" title="Восстановить">
                      <i class="fa-solid fa-rotate-left"></i>
                    </button>
                  </form>
                @endcan

                @can('force-delete-object', $object)
                  <button type="button" class="btn btn-danger btn-sm btn-delete ms-auto" title="Удалить навсегда"
                          data-action="{{ route('objects.force-delete', $object) }}"
                          data-name="{{ $object->full_title }}"
                          data-heading="Удалить навсегда?"
                          data-message="Объект «{{ $object->full_title }}» будет удалён без возможности восстановления.">
                    <i class="fa-solid fa-ban"></i>
                  </button>
                @endcan
              @endif
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </main>
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
