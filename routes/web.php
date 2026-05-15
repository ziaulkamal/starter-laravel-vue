<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Auth ─────────────────────────────────────────────────────────
Route::get('/login', fn () => Inertia::render('Auth/Login'))->name('login');
Route::get('/register', fn () => Inertia::render('Auth/Register'))->name('register');
Route::post('/logout', fn () => redirect('/login'))->name('logout');

// ── Authenticated ─────────────────────────────────────────────────
Route::middleware('web')->group(function () {

    // Dashboard
    Route::get('/', fn () => Inertia::render('Home'))->name('dashboard');

    // Profile
    Route::get('/profile', fn () => Inertia::render('Profile/Index'))->name('profile');

    // Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',        fn () => Inertia::render('Users/Index'))->name('index');
        Route::get('/create',  fn () => Inertia::render('Users/Create'))->name('create');
        Route::get('/{id}',    fn () => Inertia::render('Users/Show'))->name('show');
        Route::get('/{id}/edit', fn () => Inertia::render('Users/Edit'))->name('edit');
    });

    // Roles & Permissions
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', fn () => Inertia::render('Roles/Index'))->name('index');
    });

    Route::prefix('permissions')->name('permissions.')->group(function () {
        Route::get('/', fn () => Inertia::render('Permissions/Index'))->name('index');
    });

    Route::get('/demo/table', fn() => inertia('Components/TableDemo'));
});
