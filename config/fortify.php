<?php

use Laravel\Fortify\Features;

/*
 | Настройки Laravel Fortify (аутентификация). Недостающие параметры
 | берутся из значений пакета по умолчанию.
 */
return [

    'guard' => 'web',

    'passwords' => 'users',

    // Логин по email
    'username' => 'email',
    'email' => 'email',

    // Куда перенаправлять после входа и регистрации
    'home' => '/objects',

    // Маршруты /login, /register, /logout без префикса
    'prefix' => '',
    'domain' => null,

    'middleware' => ['web'],

    'limiters' => [
        'login' => 'login',
    ],

    'views' => true,

    // Включена только регистрация (вход и выход есть всегда)
    'features' => [
        Features::registration(),
    ],

];
