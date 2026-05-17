<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::with('roles')
            ->orderBy('name')
            ->get()
            ->map(fn (User $user) => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'avatar'        => $user->avatar,
                'is_active'     => $user->is_active,
                'last_login_at' => $user->last_login_at?->format('d M Y H:i'),
                'login_method'  => $user->login_method ?? 'password',
                'roles'         => $user->roles->pluck('name'),
            ]);

        return Inertia::render('Settings/Users/Index', [
            'users' => $users,
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', Password::defaults()],
            'role'      => ['nullable', 'string', 'exists:roles,name'],
            'is_active' => ['boolean'],
            'avatar'    => ['nullable', 'url', 'max:500'],
        ]);

        if (($data['role'] ?? '') === 'super_admin' && !auth()->user()->hasRole('super_admin')) {
            return back()->with('error', 'Hanya super_admin yang dapat memberikan role super_admin.');
        }

        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'is_active' => $data['is_active'] ?? true,
            'avatar'    => $data['avatar'] ?? null,
        ]);

        if (!empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        return back()->with('success', "User {$user->name} berhasil ditambahkan.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        if ($redirect = $this->guardSuperadmin($user)) {
            return $redirect;
        }

        $data = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'  => ['nullable', Password::defaults()],
            'role'      => ['nullable', 'string', 'exists:roles,name'],
            'is_active' => ['boolean'],
            'avatar'    => ['nullable', 'url', 'max:500'],
        ]);

        if (($data['role'] ?? '') === 'super_admin' && !auth()->user()->hasRole('super_admin')) {
            return back()->with('error', 'Hanya super_admin yang dapat memberikan role super_admin.');
        }

        $user->update([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'is_active' => $data['is_active'] ?? $user->is_active,
            'avatar'    => $data['avatar'] ?? $user->avatar,
        ]);

        if (!empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        $user->syncRoles(empty($data['role']) ? [] : [$data['role']]);

        return back()->with('success', "User {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($redirect = $this->guardSuperadmin($user)) {
            return $redirect;
        }

        $name = $user->name;
        User::query()->whereKey($user->id)->delete();

        return back()->with('success', "User {$name} berhasil dihapus.");
    }

    private function guardSuperadmin(User $target): ?RedirectResponse
    {
        if ($target->hasRole('super_admin') && !auth()->user()->hasRole('super_admin')) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengubah user super_admin.');
        }

        return null;
    }
}
