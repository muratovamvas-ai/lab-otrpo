@extends('layouts.app')

@section('title', 'Пользователи')

@section('content')
  <h1 class="h3 mb-4">Пользователи</h1>

  <div class="card shadow-sm">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th>Логин</th>
            <th>Имя</th>
            <th class="text-center">Объектов</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          @foreach ($users as $user)
            <tr>
              <td>
                <a href="{{ route('users.objects', $user) }}" class="fw-bold text-decoration-none">{{ $user->username }}</a>
                @if ($user->isAdmin())
                  <span class="badge text-bg-warning">админ</span>
                @endif
                @if ($user->is(auth()->user()))
                  <span class="badge text-bg-secondary">это вы</span>
                @endif
              </td>
              <td>{{ $user->name }}</td>
              <td class="text-center">{{ $user->messier_objects_count }}</td>
              <td class="text-end">
                <a href="{{ route('users.objects', $user) }}" class="btn btn-outline-primary btn-sm">Объекты</a>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endsection
