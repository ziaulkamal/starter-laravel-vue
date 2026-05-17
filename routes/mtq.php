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
use App\Http\Controllers\Mtq\Peserta\PengajuanEditController;
use App\Http\Controllers\Mtq\Peserta\PengajuanHapusController;
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

// ── M2: Pendaftaran Peserta (superadmin + admin + user) ───────────────
Route::middleware(['auth', 'role:superadmin|admin|user'])->prefix('mtq')->name('mtq.')->group(function () {

    // ── Peserta CRUD ──────────────────────────────────────────────────
    Route::post('peserta/bulk-destroy',     [PesertaController::class, 'bulkDestroy'])->name('peserta.bulk-destroy');
    Route::resource('peserta', PesertaController::class, ['parameters' => ['peserta' => 'peserta']])->only(['index', 'store', 'update', 'destroy']);
    Route::post('peserta/{peserta}/submit', [PesertaController::class, 'submit'])->name('peserta.submit');

    // ── Verifikasi (admin + superadmin) ──────────────────────────────
    Route::post('peserta/{peserta}/verify', [PesertaController::class, 'verify'])->name('peserta.verify')->middleware('role:superadmin|admin');
    Route::post('peserta/{peserta}/reject', [PesertaController::class, 'reject'])->name('peserta.reject')->middleware('role:superadmin|admin');

    // ── Foto Peserta ──────────────────────────────────────────────────
    Route::post('peserta/{peserta}/foto', [PesertaController::class, 'updateFoto'])->name('peserta.foto.update');

    // ── Berkas ────────────────────────────────────────────────────────
    Route::post('peserta/{peserta}/berkas', [BerkasPesertaController::class, 'store'])->name('peserta.berkas.store');
    Route::delete('berkas/{berkas}',        [BerkasPesertaController::class, 'destroy'])->name('berkas.destroy');

    // ── Pengajuan Edit ────────────────────────────────────────────────
    Route::post('peserta/{peserta}/pengajuan-edit',   [PengajuanEditController::class, 'store'])->name('peserta.pengajuan-edit.store');
    Route::get('pengajuan-edit',                      [PengajuanEditController::class, 'index'])->name('pengajuan-edit.index');

    // admin + superadmin: manajemen semua pengajuan masuk
    Route::get('pengajuan-edit/manage',               [PengajuanEditController::class, 'manage'])->name('pengajuan-edit.manage')->middleware('role:superadmin|admin');
    Route::post('pengajuan-edit/{pengajuan}/approve', [PengajuanEditController::class, 'approve'])->name('pengajuan-edit.approve')->middleware('role:superadmin|admin');
    Route::post('pengajuan-edit/{pengajuan}/reject',  [PengajuanEditController::class, 'reject'])->name('pengajuan-edit.reject')->middleware('role:superadmin|admin');

    // ── Pengajuan Hapus ───────────────────────────────────────────────
    // admin mengajukan hapus untuk peserta diverifikasi
    Route::post('peserta/{peserta}/pengajuan-hapus',   [PengajuanHapusController::class, 'store'])->name('peserta.pengajuan-hapus.store')->middleware('role:admin');

    // superadmin: manajemen semua pengajuan hapus
    Route::get('pengajuan-hapus/manage',               [PengajuanHapusController::class, 'manage'])->name('pengajuan-hapus.manage')->middleware('role:superadmin');
    Route::post('pengajuan-hapus/{pengajuan}/approve', [PengajuanHapusController::class, 'approve'])->name('pengajuan-hapus.approve')->middleware('role:superadmin');
    Route::post('pengajuan-hapus/{pengajuan}/reject',  [PengajuanHapusController::class, 'reject'])->name('pengajuan-hapus.reject')->middleware('role:superadmin');
});
