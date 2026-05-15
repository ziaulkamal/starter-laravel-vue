<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(): Response
    {
        $permissions = Permission::orderBy('name')->get(['id', 'name']);
        $roles = Role::withCount('users')
            ->with('permissions:id,name')
            ->orderBy('name')
            ->get()
            ->map(fn (Role $role) => [
                'id'              => $role->id,
                'name'            => $role->name,
                'users_count'     => $role->users_count,
                'permission_ids'  => $role->permissions->pluck('id'),
            ]);

        return Inertia::render('Settings/Roles/Index', [
            'roles'       => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'permission_ids'   => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->syncPermissions($data['permission_ids'] ?? []);

        return back()->with('success', "Permission untuk role '{$role->name}' berhasil diperbarui.");
    }
}
