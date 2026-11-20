{{--
  Комментарии к объекту и форма добавления.
  Переменные: $object (с загруженными comments.user), $friendIds — id друзей текущего пользователя.
  Комментарии друзей подсвечиваются цветом и значком.
--}}
<div id="comments" class="mt-4">
  <h2 class="h5 mb-3">Комментарии ({{ $object->comments->count() }})</h2>

  @forelse ($object->comments as $comment)
    @php
      $isMine = (int) $comment->user_id === (int) auth()->id();
      $isFriend = in_array((int) $comment->user_id, $friendIds);
    @endphp
    <div class="comment mb-2 {{ $isFriend ? 'comment-friend' : '' }}">
      <div class="d-flex flex-wrap align-items-center gap-2 small mb-1">
        <strong>{{ $comment->user->username }}</strong>
        @if ($isFriend)
          <span class="badge text-bg-warning" title="Комментарий вашего друга">
            <i class="fa-solid fa-user-group"></i> друг
          </span>
        @endif
        @if ($isMine)
          <span class="badge text-bg-secondary">вы</span>
        @endif
        <span class="text-muted">{{ $comment->created_at->diffForHumans() }}</span>

        @can('delete-comment', $comment)
          <form action="{{ route('comments.destroy', $comment) }}" method="POST" class="ms-auto"
                onsubmit="return confirm('Удалить комментарий?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm py-0" title="Удалить комментарий">
              <i class="fa-solid fa-trash"></i>
            </button>
          </form>
        @endcan
      </div>
      <div>{!! nl2br(e($comment->body)) !!}</div>
    </div>
  @empty
    <p class="text-muted">Пока нет комментариев. Будьте первым!</p>
  @endforelse

  @unless ($object->trashed())
    <form action="{{ route('comments.store', $object) }}" method="POST" class="needs-validation mt-3" novalidate>
      @csrf
      <label for="body" class="form-label">Ваш комментарий</label>
      <textarea id="body" name="body" rows="3" required minlength="2" maxlength="1000"
                class="form-control @error('body') is-invalid @enderror">{{ old('body') }}</textarea>
      <div class="invalid-feedback">@error('body') {{ $message }} @else Напишите комментарий (от 2 до 1000 символов). @enderror</div>
      <button type="submit" class="btn btn-primary mt-2">
        <i class="fa-solid fa-paper-plane"></i> Отправить
      </button>
    </form>
  @endunless
</div>
