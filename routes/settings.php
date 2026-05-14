<?php

use App\Http\Controllers\Settings\MenuController;
use Illuminate\Support\Facades\Route;

Route::prefix('settings')->name('settings.')->group(function () {

    // Route spesifik harus didaftarkan sebelum resource agar tidak
    // tertangkap oleh wildcard DELETE menus/{menu}
    Route::post('menus/reorder', [MenuController::class, 'reorder'])
        ->name('menus.reorder');

    Route::delete('menus/destroy-all', [MenuController::class, 'destroyAll'])
        ->name('menus.destroy-all');

    Route::resource('menus', MenuController::class)
        ->only(['index', 'store', 'update', 'destroy']);

});
