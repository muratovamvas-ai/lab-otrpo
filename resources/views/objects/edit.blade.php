@extends('layouts.app')

@section('title', 'Редактирование: '.$object->full_title)

@section('content')
  <h1 class="h3 mb-4">Редактирование: {{ $object->full_title }}</h1>

  @include('objects._form', [
      'action' => route('objects.update', $object),
      'method' => 'PUT',
      'submitText' => 'Сохранить',
  ])
@endsection
