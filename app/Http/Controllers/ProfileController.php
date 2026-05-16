<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(): Response
    {
        $user = Auth::user();

        return Inertia::render('Profile/Index', [
            'profileData' => [
                'name'  => $user->name,
                'email' => $user->email,
                // strip "62" prefix so input field shows "8xxxxxxxx"
                'phone' => $user->phone ? preg_replace('/^62/', '', $user->phone) : '',
            ],
        ]);
    }

    public function updateInfo(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'regex:/^62[0-9]{7,13}$/', 'unique:users,phone,' . Auth::id()],
        ]);

        Auth::user()->update([
            'name'  => $data['name'],
            'phone' => $data['phone'] ?? null,
        ]);

        return back()->with('success', 'Informasi pribadi berhasil diperbarui.');
    }

    public function updateEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'email'            => ['required', 'email', 'max:255', 'unique:users,email,' . Auth::id()],
            'current_password' => ['required', function ($attr, $val, $fail) {
                if (! Hash::check($val, Auth::user()->password)) {
                    $fail('Password saat ini tidak sesuai.');
                }
            }],
        ]);

        Auth::user()->update(['email' => $request->email]);

        return back()->with('success', 'Email berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', function ($attr, $val, $fail) {
                if (! Hash::check($val, Auth::user()->password)) {
                    $fail('Password saat ini tidak sesuai.');
                }
            }],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        Auth::user()->update(['password' => $request->password]);

        return back()->with('success', 'Kata sandi berhasil diperbarui.');
    }
}
