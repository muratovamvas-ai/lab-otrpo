<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Каталог Мессье') — Муратова, Лабораторная 3</title>

  {{-- Собранные Laravel Mix файлы (public/css, public/js). ?v= сбрасывает кеш браузера после пересборки --}}
  @php
    $assetV = fn (string $file) => asset($file).'?v='.(@filemtime(public_path($file)) ?: 1);
  @endphp
  <link rel="stylesheet" href="{{ $assetV('css/app.css') }}">
  <link rel="stylesheet" href="{{ $assetV('css/extra.css') }}">
</head>
<body>

  <div class="container py-3">

    <!-- Шапка сайта: компонент Navbar / Nav -->
    <nav class="navbar navbar-expand-md navbar-dark bg-dark site-header px-3 mb-4">
      <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('objects.index') }}">
        <span class="logo">👽</span>
        <span class="site-title">Каталог Шарля Мессье</span>
      </a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
              aria-controls="mainNav" aria-expanded="false" aria-label="Меню">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="mainNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('objects.index') ? 'active' : '' }}" href="{{ route('objects.index') }}">Объекты</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="{{ route('objects.index') }}#about">О каталоге</a>
          </li>
        </ul>
        <div class="d-flex gap-2 py-2 py-md-0">
          <a href="{{ route('objects.create') }}" class="btn btn-outline-light">
            <i class="fa-solid fa-plus"></i> Добавить
          </a>
          <a href="https://ru.wikipedia.org/wiki/Список_объектов_Мессье" target="_blank" rel="noopener" class="btn btn-primary">Оригинал</a>
        </div>
      </div>
    </nav>

    @if (session('status'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
      </div>
    @endif

    @yield('content')

    <footer class="footer text-center mt-4 pt-3">
      <p class="mb-0">Муратова — Лабораторная работа №3</p>
    </footer>
  </div>

  @stack('modals')

  <script src="{{ $assetV('js/app.js') }}"></script>
</body>
</html>
