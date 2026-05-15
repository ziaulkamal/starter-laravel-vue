<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'employees.view',
            'employees.edit',
            'employees.delete',
            'settings.menu',
            'settings.profile-menu',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        $user = Role::firstOrCreate(['name' => 'user']);
        $user->syncPermissions([]);

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'users.view',
            'users.create',
            'users.edit',
            'employees.view',
            'employees.edit',
            'settings.menu',
            'settings.profile-menu',
        ]);

        $superadmin = Role::firstOrCreate(['name' => 'superadmin']);
        $superadmin->syncPermissions($permissions);
    }
}
