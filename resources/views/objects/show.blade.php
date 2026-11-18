@extends('layouts.app')

@section('title', $object->full_title)

@section('content')
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-sm">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
          <h1 class="h4 mb-0">{{ $object->full_title }}</h1>
          <span class="badge text-bg-dark">{{ $object->type }}</span>
        </div>
        <div class="card-body" id="pageDetails">
          @include('objects._details')
        </div>
        <div class="card-footer d-flex flex-wrap gap-2">
          <a href="{{ route('objects.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left"></i> К списку
          </a>
          <a href="{{ route('objects.edit', $object) }}" class="btn btn-primary">
            <i class="fa-solid fa-pen"></i> Редактировать
          </a>
          <button type="button" class="btn btn-outline-danger ms-auto btn-delete"
                  data-action="{{ route('objects.destroy', $object) }}"
                  data-name="{{ $object->full_title }}">
            <i class="fa-solid fa-trash"></i> Удалить
          </button>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('modals')
  @include('objects._delete_modal')
@endpush
