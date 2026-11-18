@extends('layouts.app')

@section('title', 'Добавление объекта')

@section('content')
  <h1 class="h3 mb-4">Добавление объекта</h1>

  @include('objects._form', [
      'action' => route('objects.store'),
      'method' => 'POST',
      'submitText' => 'Добавить',
  ])
@endsection
