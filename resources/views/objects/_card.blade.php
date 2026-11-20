{{-- Карточка объекта в сетке. Используется в списках и в ленте. Нужна переменная $object. --}}
<div class="col">
  <div class="card object-card h-100 {{ $object->trashed() ? 'is-trashed' : '' }}">
    <div class="labeledimg">
      <img src="{{ $object->image_url }}" class="card-img-top img-fluid" alt="{{ $object->constellation }}">
      <span class="label">{{ $object->type }}</span>
      @if ($object->trashed())
        <span class="label label-deleted">Удалён</span>
      @endif
    </div>
    <div class="card-body d-flex flex-column">
      <h3 class="card-title h5">{{ $object->constellation }}</h3>
      <p class="card-text small mb-2">{{ $object->short_description }}</p>
      <p class="small text-muted mb-1">
        <i class="fa-solid fa-user"></i>
        @if ($object->user)
          <a href="{{ route('users.objects', $object->user) }}" class="text-reset">{{ $object->user->username }}</a>
        @else
          автор не указан
        @endif
        <span class="ms-2" title="Комментарии"><i class="fa-regular fa-comment"></i> {{ $object->comments_count ?? 0 }}</span>
      </p>
      <p class="small text-muted mb-3">Добавлено {{ $object->created_at?->diffForHumans() }}</p>

      <div class="mt-auto d-flex align-items-center gap-2">
        {{-- Обычная ссылка на show; JS открывает её содержимое в Modal --}}
        <a href="{{ route('objects.show', $object) }}"
           class="btn btn-primary btn-sm btn-details"
           data-title="{{ $object->full_title }}"
           @can('update-object', $object)
             @unless ($object->trashed())
               data-edit-url="{{ route('objects.edit', $object) }}"
             @endunless
           @endcan>Подробнее</a>

        @if (! $object->trashed())
          {{-- Кнопки видны только тем, у кого есть права (автор или админ) --}}
          @can('update-object', $object)
            <a href="{{ route('objects.edit', $object) }}" class="btn btn-outline-secondary btn-sm" title="Редактировать">
              <i class="fa-solid fa-pen"></i>
            </a>
          @endcan

          @can('delete-object', $object)
            <button type="button" class="btn btn-outline-danger btn-sm btn-delete ms-auto" title="Удалить"
                    data-action="{{ route('objects.destroy', $object) }}"
                    data-name="{{ $object->full_title }}">
              <i class="fa-solid fa-trash"></i>
            </button>
          @endcan
        @else
          {{-- Удалённый объект: только администратор --}}
          @can('restore-object', $object)
            <form action="{{ route('objects.restore', $object) }}" method="POST" class="d-inline">
              @csrf
              @method('PATCH')
              <button type="submit" class="btn btn-outline-success btn-sm" title="Восстановить">
                <i class="fa-solid fa-rotate-left"></i>
              </button>
            </form>
          @endcan

          @can('force-delete-object', $object)
            <button type="button" class="btn btn-danger btn-sm btn-delete ms-auto" title="Удалить навсегда"
                    data-action="{{ route('objects.force-delete', $object) }}"
                    data-name="{{ $object->full_title }}"
                    data-heading="Удалить навсегда?"
                    data-message="Объект «{{ $object->full_title }}» будет удалён без возможности восстановления.">
              <i class="fa-solid fa-ban"></i>
            </button>
          @endcan
        @endif
      </div>
    </div>
  </div>
</div>
