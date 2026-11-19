@extends('layouts.app')

@section('title', 'Вход')

@section('content')
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
      <div class="card shadow-sm">
        <div class="card-header"><h1 class="h4 mb-0">Вход</h1></div>
        <div class="card-body">
          <form action="{{ route('login') }}" method="POST" class="needs-validation" novalidate>
            @csrf

            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" id="email" name="email" required autofocus autocomplete="username"
                     class="form-control @error('email') is-invalid @enderror"
                     value="{{ old('email') }}">
              <div class="invalid-feedback">
                @error('email')
                  {{-- Fortify возвращает здесь и сообщение «неверный email или пароль» --}}
                  {{ $message }}
                @else
                  Введите корректный email.
                @enderror
              </div>
            </div>

            <div class="mb-3">
              <label for="password" class="form-label">Пароль</label>
              <input type="password" id="password" name="password" required autocomplete="current-password"
                     class="form-control @error('password') is-invalid @enderror">
              <div class="invalid-feedback">@error('password') {{ $message }} @else Введите пароль. @enderror</div>
            </div>

            <div class="form-check mb-3">
              <input type="checkbox" id="remember" name="remember" value="1" class="form-check-input">
              <label for="remember" class="form-check-label">Запомнить меня</label>
            </div>

            <button type="submit" class="btn btn-primary w-100">Войти</button>
          </form>
        </div>
        <div class="card-footer text-center small">
          Нет аккаунта? <a href="{{ route('register') }}">Зарегистрируйтесь</a>
        </div>
      </div>
    </div>
  </div>
@endsection
