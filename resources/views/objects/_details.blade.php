{{-- Подробности об объекте. Используется и в Modal (AJAX), и на странице show. --}}
<img src="{{ $object->image_url }}" class="img-fluid rounded mb-3 w-100" alt="{{ $object->constellation }}">

<div class="details-text">
  {!! $object->details_html !!}
</div>

<hr>
<dl class="row small mb-0">
  <dt class="col-sm-4">Тип объекта</dt>
  <dd class="col-sm-8">{{ $object->type }}</dd>
  <dt class="col-sm-4">Внесён в каталог</dt>
  <dd class="col-sm-8">{{ $object->catalogued_at_text }}</dd>
  <dt class="col-sm-4">Добавил</dt>
  <dd class="col-sm-8 mb-0">
    @if ($object->user)
      <a href="{{ route('users.objects', $object->user) }}">{{ $object->user->username }}</a>
    @else
      —
    @endif
  </dd>
</dl>
