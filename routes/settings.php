<?php

use App\Http\Controllers\Settings\MenuController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->name('settings.')->group(function () {

    Route::resource('menus', MenuController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::post('menus/reorder', [MenuController::class, 'reorder'])
        ->name('menus.reorder');

});
