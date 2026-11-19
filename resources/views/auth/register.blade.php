@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-header"><h1 class="h4 mb-0">Регистрация</h1></div>
        <div class="card-body">
          <form action="{{ route('register') }}" method="POST" class="needs-validation" novalidate>
            @csrf

            <div class="mb-3">
              <label for="name" class="form-label">Имя</label>
              <input type="text" id="name" name="name" required maxlength="255" autocomplete="name"
                     class="form-control @error('name') is-invalid @enderror"
                     value="{{ old('name') }}">
              <div class="invalid-feedback">@error('name') {{ $message }} @else Введите имя. @enderror</div>
            </div>

            <div class="mb-3">
              <label for="username" class="form-label">Логин (для адреса страницы)</label>
              <input type="text" id="username" name="username" required minlength="3" maxlength="30"
                     pattern="[A-Za-z0-9_]+" autocomplete="username"
                     class="form-control @error('username') is-invalid @enderror"
                     value="{{ old('username') }}" placeholder="например: ivan_petrov">
              <div class="form-text">Латинские буквы, цифры и «_», от 3 до 30 символов. Появится в адресе: /users/логин/objects</div>
              <div class="invalid-feedback">@error('username') {{ $message }} @else Логин: от 3 до 30 символов, только латиница, цифры и «_». @enderror</div>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" id="email" name="email" required maxlength="255" autocomplete="email"
                     class="form-control @error('email') is-invalid @enderror"
                     value="{{ old('email') }}">
              <div class="invalid-feedback">@error('email') {{ $message }} @else Введите корректный email. @enderror</div>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Пароль (не менее 8 символов)</label>
              <input type="password" id="password" name="password" required minlength="8" autocomplete="new-password"
                     class="form-control @error('password') is-invalid @enderror">
              <div class="invalid-feedback">@error('password') {{ $message }} @else Пароль должен содержать не менее 8 символов. @enderror</div>
            </div>

            <div class="mb-3">
              <label for="password_confirmation" class="form-label">Повторите пароль</label>
              <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8"
                     autocomplete="new-password" class="form-control">
              <div class="invalid-feedback">Повторите пароль.</div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
          </form>
        </div>
        <div class="card-footer text-center small">
          Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a>
        </div>
      </div>
    </div>
  </div>
@endsection
