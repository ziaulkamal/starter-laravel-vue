<?php

use App\Http\Controllers\Admin\MustahikController as AdminMustahikController;
use App\Http\Controllers\Admin\SenifController;
use App\Http\Controllers\Gampong\PengajuanBantuanController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\BerkasController;
use App\Http\Controllers\Gampong\MustahikController as GampongMustahikController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Settings\PendingRegistrationController;
use App\Http\Controllers\Settings\PermissionScanController;
use App\Http\Controllers\Settings\RegistrationLogController;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\Settings\UserController;
use App\Http\Controllers\WilayahController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Guest only ────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register'])->middleware('throttle:3,60');

    // Social Auth redirects (guest only)
    Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/sso',    [SocialAuthController::class, 'redirectToSso'])->name('auth.sso');
});

// Social Auth callbacks (no guest guard — OAuth provider redirects back here)
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/auth/sso/callback',    [SocialAuthController::class, 'handleSsoCallback'])->name('auth.sso.callback');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/reset',  [AuthController::class, 'reset'])->name('reset');

// ── Authenticated ─────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/', fn () => Inertia::render('Home'))->name('dashboard');

    Route::get('/profile',             [ProfileController::class, 'show'])->name('profile');
    Route::put('/profile/info',        [ProfileController::class, 'updateInfo'])->name('profile.info');
    Route::put('/profile/email',       [ProfileController::class, 'updateEmail'])->name('profile.email');
    Route::put('/profile/password',    [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Settings: Users (admin & superadmin)
    Route::middleware('permission:users.view')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/users',           [UserController::class, 'index'])->name('users.index');
        Route::post('/users',          [UserController::class, 'store'])->middleware('permission:users.create')->name('users.store');
        Route::put('/users/{user}',    [UserController::class, 'update'])->middleware('permission:users.edit')->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete')->name('users.destroy');
    });

    // Settings: Roles (super_admin only)
    Route::middleware('role:super_admin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/roles',                      [RoleController::class, 'index'])->name('roles.index');
        Route::put('/roles/{role}',               [RoleController::class, 'update'])->name('roles.update');
        Route::post('/permissions/scan',          [PermissionScanController::class, 'scan'])->name('permissions.scan');
        Route::post('/permissions/sync',          [PermissionScanController::class, 'sync'])->name('permissions.sync');
        Route::delete('/permissions/reset',       [PermissionScanController::class, 'reset'])->name('permissions.reset');

        // Pending registrations approval
        Route::get('/pending-registrations', [PendingRegistrationController::class, 'index'])->name('pending-registrations.index');
        Route::post('/pending-registrations/{pending}/approve', [PendingRegistrationController::class, 'approve'])->name('pending-registrations.approve');
        Route::post('/pending-registrations/{pending}/reject',  [PendingRegistrationController::class, 'reject'])->name('pending-registrations.reject');

        // Registration logs (JSON)
        Route::get('/registration-logs', [RegistrationLogController::class, 'index'])->name('registration-logs.index');
    });

    // Internal API: notifications (polling)
    Route::prefix('api/internal')->name('api.')->group(function () {
        Route::get('/notifications',              [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read',   [NotificationController::class, 'markRead'])->name('notifications.read');
        Route::post('/notifications/read-all',    [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    });

    Route::get('/demo/table', fn () => Inertia::render('Components/TableDemo'));

    // ── Wilayah API (cascade dropdown) ───────────────────────────────
    Route::prefix('api/wilayah')->name('api.wilayah.')->group(function () {
        Route::get('/kecamatan', [WilayahController::class, 'kecamatan'])->name('kecamatan');
        Route::get('/desa',      [WilayahController::class, 'desa'])->name('desa');
        Route::get('/info',      [WilayahController::class, 'info'])->name('info');
    });

    // ── Berkas Mustahik ──────────────────────────────────────────────
    Route::prefix('berkas')->name('berkas.')->group(function () {
        Route::post('/',           [BerkasController::class, 'store'])->name('store');
        Route::get('/{berkas}/download', [BerkasController::class, 'download'])->name('download');
        Route::delete('/{berkas}', [BerkasController::class, 'destroy'])->name('destroy');
    });

    // ── Admin Kabupaten ──────────────────────────────────────────────
    Route::middleware('role:admin_kabupaten|super_admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

        // Master Senif (Substansi Kategori Asnaf)
        Route::get('/senif',                    [SenifController::class, 'index'])->name('senif.index');
        Route::post('/senif',                   [SenifController::class, 'store'])->name('senif.store');
        Route::put('/senif/{senif}',             [SenifController::class, 'update'])->name('senif.update');
        Route::patch('/senif/{senif}/toggle',   [SenifController::class, 'toggle'])->name('senif.toggle');
        Route::delete('/senif/{senif}',         [SenifController::class, 'destroy'])->name('senif.destroy');

        // Mustahik
        Route::get('/mustahik',                         [AdminMustahikController::class, 'index'])->name('mustahik.index');
        Route::get('/mustahik/create',                  [AdminMustahikController::class, 'create'])->name('mustahik.create');
        Route::post('/mustahik',                        [AdminMustahikController::class, 'store'])->name('mustahik.store');
        Route::get('/mustahik/{mustahik}',              [AdminMustahikController::class, 'show'])->name('mustahik.show');
        Route::get('/mustahik/{mustahik}/edit',         [AdminMustahikController::class, 'edit'])->name('mustahik.edit');
        Route::put('/mustahik/{mustahik}',              [AdminMustahikController::class, 'update'])->name('mustahik.update');
        Route::post('/mustahik/{mustahik}/nonaktifkan', [AdminMustahikController::class, 'nonaktifkan'])->name('mustahik.nonaktifkan');
        Route::post('/mustahik/{mustahik}/aktifkan',    [AdminMustahikController::class, 'aktifkan'])->name('mustahik.aktifkan');
    });

    // ── Admin Gampong ────────────────────────────────────────────────
    Route::middleware('role:admin_gampong')
        ->prefix('gampong')
        ->name('gampong.')
        ->group(function () {

        // Mustahik
        Route::get('/mustahik',                 [GampongMustahikController::class, 'index'])->name('mustahik.index');
        Route::get('/mustahik/create',          [GampongMustahikController::class, 'create'])->name('mustahik.create');
        Route::post('/mustahik',                [GampongMustahikController::class, 'store'])->name('mustahik.store');
        Route::get('/mustahik/{mustahik}',      [GampongMustahikController::class, 'show'])->name('mustahik.show');
        Route::get('/mustahik/{mustahik}/edit', [GampongMustahikController::class, 'edit'])->name('mustahik.edit');
        Route::put('/mustahik/{mustahik}',      [GampongMustahikController::class, 'update'])->name('mustahik.update');

        // Pengajuan bantuan (gampong)
        Route::post('/mustahik/{mustahik}/ajukan',        [PengajuanBantuanController::class, 'store'])->name('pengajuan.store');
        Route::post('/pengajuan/{pengajuan}/sanggah',     [PengajuanBantuanController::class, 'sanggah'])->name('pengajuan.sanggah');
    });
});
