<?php

use App\Http\Controllers\MessierObjectController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/objects');

// Resource-маршруты: index, create, store, show, edit, update, destroy
// Имена маршрутов: objects.index, objects.create, objects.store, ...
// Параметр маршрута называем {object}, чтобы работало неявное связывание модели.
Route::get('objects/check-name', [MessierObjectController::class, 'checkName'])->name('objects.check-name');
Route::resource('objects', MessierObjectController::class)
    ->parameters(['objects' => 'object']);
