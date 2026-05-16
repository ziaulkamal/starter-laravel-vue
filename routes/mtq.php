<?php

use App\Http\Controllers\Mtq\Master\CabangController;
use App\Http\Controllers\Mtq\Master\GolonganController;
use App\Http\Controllers\Mtq\Master\KriteriaController;
use App\Http\Controllers\Mtq\Master\KafilahController;
use App\Http\Controllers\Mtq\Master\VenueController;
use App\Http\Controllers\Mtq\Master\KonfigurasiController;
use App\Http\Controllers\Mtq\Master\ResetController;
use App\Http\Controllers\Mtq\Peserta\PesertaController;
use App\Http\Controllers\Mtq\Peserta\BerkasPesertaController;
use App\Http\Controllers\Mtq\WilayahController;
use Illuminate\Support\Facades\Route;

// ── Wilayah API (JSON, auth required) ────────────────────────────────
Route::middleware('auth')->prefix('api/wilayah')->name('api.wilayah.')->group(function () {
    Route::get('provinsi',  [WilayahController::class, 'provinsi'])->name('provinsi');
    Route::get('kabupaten', [WilayahController::class, 'kabupaten'])->name('kabupaten');
    Route::get('kecamatan', [WilayahController::class, 'kecamatan'])->name('kecamatan');
    Route::get('kelurahan', [WilayahController::class, 'kelurahan'])->name('kelurahan');
});

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

// ── M2: Pendaftaran Peserta (superadmin + admin) ──────────────────────
Route::middleware(['auth', 'role:superadmin|admin'])->prefix('mtq')->name('mtq.')->group(function () {
    Route::post('peserta/bulk-destroy',        [PesertaController::class,       'bulkDestroy'])->name('peserta.bulk-destroy');
    Route::resource('peserta', PesertaController::class, ['parameters' => ['peserta' => 'peserta']])->only(['index', 'store', 'update', 'destroy']);
    Route::post('peserta/{peserta}/submit',    [PesertaController::class,       'submit'])->name('peserta.submit');
    Route::post('peserta/{peserta}/verify',    [PesertaController::class,       'verify'])->name('peserta.verify')->middleware('role:superadmin');
    Route::post('peserta/{peserta}/reject',    [PesertaController::class,       'reject'])->name('peserta.reject')->middleware('role:superadmin');
    Route::post('peserta/{peserta}/berkas',    [BerkasPesertaController::class, 'store'])->name('peserta.berkas.store');
    Route::delete('berkas/{berkas}',           [BerkasPesertaController::class, 'destroy'])->name('berkas.destroy');
});
