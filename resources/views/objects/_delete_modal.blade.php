{{--
  Окно подтверждения удаления. Адрес формы и тексты подставляет JS (resources/js/objects.js)
  из data-атрибутов кнопки: data-action, data-name, data-heading, data-message.
--}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="deleteForm" method="POST" action="#" class="modal-content">
      @csrf
      @method('DELETE')
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalTitle">Удалить объект?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
      </div>
      <div class="modal-body" id="deleteMessage">
        Объект будет удалён из каталога.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
        <button type="submit" class="btn btn-danger"><i class="fa-solid fa-trash"></i> Удалить</button>
      </div>
    </form>
  </div>
</div>
