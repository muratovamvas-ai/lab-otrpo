{{-- Окно «Подробнее» (содержимое грузится AJAX из show) и окно подтверждения удаления --}}
  <!-- Одно модальное окно: содержимое подгружается из действия show (AJAX) -->
  <div class="modal fade" id="objectModal" tabindex="-1" aria-labelledby="objectModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="objectModalTitle"></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
        </div>
        <div class="modal-body"></div>
        <div class="modal-footer justify-content-between">
          <small class="text-muted">← → — переключение объектов</small>
          <div class="d-flex flex-wrap gap-2">
            <a id="modalShowLink" href="#" class="btn btn-outline-primary">Открыть страницу</a>
            <a id="modalEditLink" href="#" class="btn btn-outline-secondary"><i class="fa-solid fa-pen"></i> Изменить</a>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  @include('objects._delete_modal')
