<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Settings\PendingRegistrationController;
use App\Http\Controllers\Settings\RegistrationLogController;
use App\Http\Controllers\Settings\RoleController;
use App\Http\Controllers\Settings\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ── Guest only ────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->middleware('throttle:2,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register'])->middleware('throttle:3,60');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/reset',  [AuthController::class, 'reset'])->name('reset');

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
});
