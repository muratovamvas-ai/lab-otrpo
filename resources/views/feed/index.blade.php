@extends('layouts.app')

@section('title', 'Лента друзей')

@section('content')
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h2 mb-0">Лента друзей</h1>
    <a href="{{ route('users.index') }}" class="btn btn-outline-primary">
      <i class="fa-solid fa-user-plus"></i> Найти друзей
    </a>
  </div>

  <p class="text-muted small">
    Новые объекты ваших друзей: сначала самые свежие.
    Друзей: {{ $friends->count() }}
    @foreach ($friends as $friend)
      <a href="{{ route('users.objects', $friend) }}" class="badge text-bg-light text-decoration-none">{{ $friend->username }}</a>
    @endforeach
  </p>

  @if ($friends->isEmpty())
    <div class="alert alert-info">
      У вас пока нет друзей. Добавьте их на странице <a href="{{ route('users.index') }}">«Пользователи»</a> —
      и здесь появятся их объекты.
    </div>
  @elseif ($objects->isEmpty())
    <div class="alert alert-info">У ваших друзей пока нет добавленных объектов.</div>
  @endif

  <main id="objects" class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xxl-4 row-cols-xxxl-5 g-3 g-xl-4">
    @foreach ($objects as $object)
      @include('objects._card')
    @endforeach
  </main>
@endsection

@push('modals')
  @include('objects._modals')
@endpush
