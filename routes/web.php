<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\Settings\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Guest only ────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ── Authenticated ─────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/', fn () => Inertia::render('Home'))->name('dashboard');
    Route::get('/profile', fn () => Inertia::render('Profile/Index'))->name('profile');

    // Settings: Users (admin & superadmin)
    Route::middleware('permission:users.view')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/users',           [UserController::class, 'index'])->name('users.index');
        Route::post('/users',          [UserController::class, 'store'])->middleware('permission:users.create')->name('users.store');
        Route::put('/users/{user}',    [UserController::class, 'update'])->middleware('permission:users.edit')->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete')->name('users.destroy');
    });

    // Settings: Roles (superadmin only)
    Route::middleware('role:superadmin')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/roles',           [RoleController::class, 'index'])->name('roles.index');
        Route::put('/roles/{role}',    [RoleController::class, 'update'])->name('roles.update');
    });

    Route::get('/demo/table', fn () => Inertia::render('Components/TableDemo'));
});
