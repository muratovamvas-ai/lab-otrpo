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
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
      <span class="text-muted small">
        {{ $owner->name }} ·
        <a href="{{ route('users.index') }}">все пользователи</a> ·
        <a href="{{ route('objects.index') }}">все объекты</a>
      </span>

      {{-- Дружба с владельцем списка (на своей странице кнопки нет) --}}
      @unless ($owner->is(auth()->user()))
        @if ($isFriend)
          <span class="badge text-bg-success"><i class="fa-solid fa-user-group"></i> ваш друг</span>
          <form action="{{ route('friends.destroy', $owner) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">Убрать из друзей</button>
          </form>
        @else
          <form action="{{ route('friends.store', $owner) }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-outline-success btn-sm">
              <i class="fa-solid fa-user-plus"></i> Добавить в друзья
            </button>
          </form>
        @endif
      @endunless
    </div>
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
      @include('objects._card')
    @endforeach
  </main>
@endsection

@push('modals')
  @include('objects._modals')
@endpush
