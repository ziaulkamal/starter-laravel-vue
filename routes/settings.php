<?php

use App\Http\Controllers\Settings\MenuController;
use App\Http\Controllers\Settings\ProfileMenuController;
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

    // ── Profile Menu ──────────────────────────────────────────────
    Route::post('profile-menu/reorder', [ProfileMenuController::class, 'reorder'])
        ->name('profile-menu.reorder');

    Route::delete('profile-menu/destroy-all', [ProfileMenuController::class, 'destroyAll'])
        ->name('profile-menu.destroy-all');

    Route::resource('profile-menu', ProfileMenuController::class, ['parameters' => ['profile-menu' => 'profileMenuItem']])
        ->only(['index', 'store', 'update', 'destroy']);

});
