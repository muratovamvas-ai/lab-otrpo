// jQuery и Bootstrap подключаются через npm и собираются Laravel Mix
import $ from 'jquery';
import * as bootstrap from 'bootstrap';

window.$ = window.jQuery = $;
window.bootstrap = bootstrap;

// Laravel CSRF-токен для всех AJAX-запросов jQuery
$.ajaxSetup({
  headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
});
