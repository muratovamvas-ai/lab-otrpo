// Сборка ресурсов через Laravel Mix (обёртка над Webpack).
// Исходники лежат в resources/, результат — в public/.
const mix = require('laravel-mix');

mix
  // JS: jQuery + Bootstrap + наш код
  .js('resources/js/app.js', 'public/js')

  // SCSS: Bootstrap (с нашими переменными) + собственные стили
  .sass('resources/sass/app.scss', 'public/css', {
    sassOptions: {
      quietDeps: true, // не показывать предупреждения из исходников Bootstrap
      silenceDeprecations: ['import', 'global-builtin', 'color-functions', 'if-function', 'mixed-decls'],
    },
  })

  // Обычный CSS-файл тоже собираем (как в ЛР2) — склеится в public/css/extra.css
  .postCss('resources/css/extra.css', 'public/css')

  // Шрифты иконок FontAwesome
  .copy('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/fonts')

  // Не переписывать url() внутри SCSS (шрифты копируем сами)
  .options({ processCssUrls: false });

// Чтобы в консоли не было лишнего шума
mix.disableNotifications();
