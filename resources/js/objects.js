import $ from 'jquery';
import { Modal, Popover } from 'bootstrap';

$(function () {
  // ---------- Валидация форм на стороне HTML (HTML5 + Bootstrap) ----------
  $('form.needs-validation').on('submit', function (e) {
    if (!this.checkValidity()) {
      e.preventDefault();
      e.stopPropagation();
    }
    $(this).addClass('was-validated');
  });

  // ---------- Количество слов в текстовых полях (data-min-words / data-max-words) ----------
  const countWords = text => (text.match(/[\p{L}\p{N}]+/gu) || []).length;

  $('textarea[data-min-words]').each(function () {
    const field = this;
    const $field = $(this);
    const min = Number($field.data('min-words'));
    const max = Number($field.data('max-words'));
    const emptyText = $field.data('empty-message');
    const $counter = $field.siblings('.word-counter');
    const $feedback = $field.siblings('.invalid-feedback');

    const checkWords = function () {
      const n = countWords($field.val());
      let message = '';
      if (n < min) message = `Слишком коротко: нужно от ${min} до ${max} слов (сейчас ${n}).`;
      if (n > max) message = `Слишком длинно: нужно от ${min} до ${max} слов (сейчас ${n}).`;

      field.setCustomValidity(message); // поле неверное, пока message не пустой
      $counter.text(`Слов: ${n} (нужно от ${min} до ${max})`);
      $feedback.text(message || emptyText);
    };

    $field.on('input', checkWords);
    checkWords();
  });

    // ---------- Проверка уникальности обозначения на лету (AJAX) ----------
  const $nameField = $('#name[data-check-url]');
  if ($nameField.length) {
    const field = $nameField[0];
    const $feedback = $nameField.siblings('.invalid-feedback');
    const emptyText = $nameField.data('empty-message');
    const takenText = 'Объект с таким обозначением уже есть в каталоге.';
    const normalize = value => value.replace(/\s+/g, '').toUpperCase();
    let timer = null;

    const checkName = function () {
      const name = normalize($nameField.val());
      if (name === '') {
        field.setCustomValidity('');
        $feedback.text(emptyText);
        return;
      }

      $.getJSON($nameField.data('check-url'), { name: name, ignore: $nameField.data('ignore-id') || '' })
        .done(function (response) {
          // пока шёл запрос, текст мог измениться — тогда ответ устарел
          if (normalize($nameField.val()) !== name) return;

          field.setCustomValidity(response.taken ? takenText : '');
          $feedback.text(response.taken ? takenText : emptyText);

          // подсвечиваем сразу, если пользователь уже побывал в этом поле
          if ($nameField.data('touched')) {
            const valid = field.checkValidity();
            $nameField.toggleClass('is-invalid', !valid).toggleClass('is-valid', valid);
          }
        });
    };

    // при вводе: сбрасываем старую ошибку и проверяем через 0,4 с после паузы
    $nameField.on('input', function () {
      field.setCustomValidity('');
      clearTimeout(timer);
      timer = setTimeout(checkName, 400);
    });

    // при уходе из поля проверяем сразу
    $nameField.on('blur', function () {
      clearTimeout(timer);
      checkName();
    });
  }

  // ---------- Проверка полей «на лету» ----------
  // Поле проверяется, когда пользователь ушёл из него (blur), выбрал значение (change)
  // и при каждом вводе — но только после первого ухода из поля.
  $('form.needs-validation').find('input, textarea, select').on('input change blur', function (e) {
    if (e.type === 'blur') {
      $(this).data('touched', true);
    }
    if (!$(this).data('touched') && e.type === 'input') {
      return;
    }
    const valid = this.checkValidity();
    $(this).toggleClass('is-invalid', !valid).toggleClass('is-valid', valid);
  });

  // ---------- Предпросмотр выбранной картинки в форме ----------
  $('#image').on('change', function () {
    const file = this.files && this.files[0];
    if (!file) return;
    $('#imagePreview').attr('src', URL.createObjectURL(file));
  });

  // ---------- Подтверждение удаления ----------
  const deleteModalEl = document.getElementById('deleteModal');
  if (deleteModalEl) {
    const deleteModal = new Modal(deleteModalEl);
    $('.btn-delete').on('click', function () {
      $('#deleteForm').attr('action', $(this).data('action'));
      $('#deleteName').text($(this).data('name'));
      deleteModal.show();
    });
  }

  // ---------- Подсказки (Popover) на странице show ----------
  $('#pageDetails .hint').each(function () {
    new Popover(this, { trigger: 'hover focus', placement: 'top' });
    $(this).attr('tabindex', 0);
  });

  // ---------- Модальное окно «Подробнее» ----------
  const modalEl = document.getElementById('objectModal');
  if (!modalEl) return;

  const modal = new Modal(modalEl);
  const $body = $(modalEl).find('.modal-body');
  let popovers = [];
  let currentIndex = 0;

  function removePopovers() {
    popovers.forEach(p => p.dispose());
    popovers = [];
  }

  function createPopovers() {
    $body.find('.hint').each(function () {
      popovers.push(new Popover(this, { trigger: 'hover focus', placement: 'top' }));
      $(this).attr('tabindex', 0);
    });
  }

  // Загружает blade-представление действия show (AJAX) в модальное окно
  function loadObject(index) {
    const $btn = $('.btn-details').eq(index);
    if (!$btn.length) return;
    currentIndex = index;

    removePopovers();
    $('#objectModalTitle').text($btn.data('title'));
    $('#modalShowLink').attr('href', $btn.attr('href'));
    $('#modalEditLink').attr('href', $btn.data('edit-url'));
    $body.addClass('fading');

    $.get($btn.attr('href'))
      .done(function (html) {
        $body.html(html).removeClass('fading');
        createPopovers();
      })
      .fail(function () {
        $body.html('<p class="text-danger mb-0">Не удалось загрузить данные объекта.</p>').removeClass('fading');
      });
  }

  // Кнопка «Подробнее» — обычная ссылка на show; с JS открываем её в окне
  $('.btn-details').on('click', function (e) {
    e.preventDefault();
    loadObject($('.btn-details').index(this));
    modal.show();
  });

  // Стрелки ←/→ переключают объекты по кругу, пока окно открыто
  $(document).on('keydown', function (e) {
    if (!modalEl.classList.contains('show')) return;
    const total = $('.btn-details').length;
    if (e.key === 'ArrowRight') loadObject((currentIndex + 1) % total);
    if (e.key === 'ArrowLeft') loadObject((currentIndex - 1 + total) % total);
  });

  modalEl.addEventListener('hidden.bs.modal', removePopovers);
});