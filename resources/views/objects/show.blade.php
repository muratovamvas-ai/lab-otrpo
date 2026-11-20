@extends('layouts.app')

@section('title', $object->full_title)

@section('content')
  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow-sm {{ $object->trashed() ? 'is-trashed' : '' }}">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
          <h1 class="h4 mb-0">{{ $object->full_title }}</h1>
          <div class="d-flex gap-2">
            @if ($object->trashed())
              <span class="badge text-bg-danger">Удалён</span>
            @endif
            <span class="badge text-bg-dark">{{ $object->type }}</span>
          </div>
        </div>
        <div class="card-body" id="pageDetails">
          @include('objects._details')
          @include('objects._comments')
        </div>
        <div class="card-footer d-flex flex-wrap gap-2">
          <a href="{{ route('objects.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left"></i> К списку
          </a>

          @if (! $object->trashed())
            @can('update-object', $object)
              <a href="{{ route('objects.edit', $object) }}" class="btn btn-primary">
                <i class="fa-solid fa-pen"></i> Редактировать
              </a>
            @endcan

            @can('delete-object', $object)
              <button type="button" class="btn btn-outline-danger ms-auto btn-delete"
                      data-action="{{ route('objects.destroy', $object) }}"
                      data-name="{{ $object->full_title }}">
                <i class="fa-solid fa-trash"></i> Удалить
              </button>
            @endcan
          @else
            @can('restore-object', $object)
              <form action="{{ route('objects.restore', $object) }}" method="POST" class="d-inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-success">
                  <i class="fa-solid fa-rotate-left"></i> Восстановить
                </button>
              </form>
            @endcan

            @can('force-delete-object', $object)
              <button type="button" class="btn btn-danger ms-auto btn-delete"
                      data-action="{{ route('objects.force-delete', $object) }}"
                      data-name="{{ $object->full_title }}"
                      data-heading="Удалить навсегда?"
                      data-message="Объект «{{ $object->full_title }}» будет удалён без возможности восстановления.">
                <i class="fa-solid fa-ban"></i> Удалить навсегда
              </button>
            @endcan
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection

@push('modals')
  @include('objects._delete_modal')
@endpush
