<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Kafilah;
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
        $users = User::with(['roles', 'kafilah'])
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
                'kafilah_id'    => $user->kafilah_id,
                'kafilah_nama'  => $user->kafilah?->nama_kabupaten,
            ]);

        return Inertia::render('Settings/Users/Index', [
            'users'    => $users,
            'roles'    => Role::orderBy('name')->pluck('name'),
            'kafilahs' => Kafilah::orderBy('nama_kabupaten')->get(['id', 'nama_kabupaten']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', 'unique:users,email'],
            'password'    => ['required', Password::defaults()],
            'role'        => ['nullable', 'string', 'exists:roles,name'],
            'is_active'   => ['boolean'],
            'avatar'      => ['nullable', 'url', 'max:500'],
            'kafilah_id'  => ['nullable', 'integer', 'exists:kafilahs,id'],
        ]);

        if (($data['role'] ?? '') === 'superadmin' && !auth()->user()->hasRole('superadmin')) {
            return back()->with('error', 'Hanya superadmin yang dapat memberikan role superadmin.');
        }

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'is_active'  => $data['is_active'] ?? true,
            'avatar'     => $data['avatar'] ?? null,
            'kafilah_id' => ($data['role'] ?? '') === 'user' ? ($data['kafilah_id'] ?? null) : null,
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
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'    => ['nullable', Password::defaults()],
            'role'        => ['nullable', 'string', 'exists:roles,name'],
            'is_active'   => ['boolean'],
            'avatar'      => ['nullable', 'url', 'max:500'],
            'kafilah_id'  => ['nullable', 'integer', 'exists:kafilahs,id'],
        ]);

        if (($data['role'] ?? '') === 'superadmin' && !auth()->user()->hasRole('superadmin')) {
            return back()->with('error', 'Hanya superadmin yang dapat memberikan role superadmin.');
        }

        $user->update([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'is_active'  => $data['is_active'] ?? $user->is_active,
            'avatar'     => $data['avatar'] ?? $user->avatar,
            'kafilah_id' => ($data['role'] ?? '') === 'user' ? ($data['kafilah_id'] ?? null) : null,
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
        if ($target->hasRole('superadmin') && !auth()->user()->hasRole('superadmin')) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengubah user superadmin.');
        }

        return null;
    }
}
