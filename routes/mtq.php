<?php

use App\Http\Controllers\Mtq\Master\CabangController;
use App\Http\Controllers\Mtq\Master\GolonganController;
use App\Http\Controllers\Mtq\Master\KriteriaController;
use App\Http\Controllers\Mtq\Master\KafilahController;
use App\Http\Controllers\Mtq\Master\VenueController;
use App\Http\Controllers\Mtq\Master\KonfigurasiController;
use App\Http\Controllers\Mtq\Master\ResetController;
use Illuminate\Support\Facades\Route;

// ── M1: Master Data (Superadmin only) ────────────────────────────────
Route::middleware(['auth', 'role:superadmin'])->prefix('mtq/master')->name('mtq.master.')->group(function () {

    Route::post('cabang/bulk-destroy',   [CabangController::class,   'bulkDestroy'])->name('cabang.bulk-destroy');
    Route::post('golongan/bulk-destroy', [GolonganController::class, 'bulkDestroy'])->name('golongan.bulk-destroy');
    Route::post('kriteria/bulk-destroy', [KriteriaController::class, 'bulkDestroy'])->name('kriteria.bulk-destroy');
    Route::post('kafilah/bulk-destroy',  [KafilahController::class,  'bulkDestroy'])->name('kafilah.bulk-destroy');
    Route::post('venue/bulk-destroy',    [VenueController::class,    'bulkDestroy'])->name('venue.bulk-destroy');

    Route::resource('cabang',   CabangController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('golongan', GolonganController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('kriteria', KriteriaController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('kafilah',  KafilahController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('venue',    VenueController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::get('konfigurasi',  [KonfigurasiController::class, 'show'])->name('konfigurasi.show');
    Route::put('konfigurasi',  [KonfigurasiController::class, 'update'])->name('konfigurasi.update');

    Route::post('reset', [ResetController::class, 'reset'])->name('reset');
});
