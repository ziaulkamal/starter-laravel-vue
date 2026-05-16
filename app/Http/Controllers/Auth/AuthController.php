<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PendingRegistration;
use App\Models\User;
use App\Notifications\NewRegistrationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        $request->session()->regenerate();

        Auth::user()->update(['last_login_at' => now(), 'login_method' => 'password']);

        return redirect()->intended('/');
    }

    public function showRegister(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email', 'unique:pending_registrations,email'],
            'phone'    => ['nullable', 'string', 'regex:/^62[0-9]{7,13}$/', 'unique:users,phone', 'unique:pending_registrations,phone'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $pending = PendingRegistration::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'phone'      => $data['phone'] ?? null,
            'password'   => $data['password'],
            'ip_address' => $request->ip(),
        ]);

        User::role('superadmin')->each(
            fn (User $admin) => $admin->notify(new NewRegistrationNotification($pending))
        );

        return redirect('/login')->with('success', 'Pendaftaran berhasil! Akun Anda sedang menunggu persetujuan admin.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function reset(Request $request): RedirectResponse
    {
        Cache::forget('app.menu');
        Cache::forget('app.profile_menu');

        if (Auth::check()) {
            Auth::logout();
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
