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

        // Auto-generate permissions dari config/modules.php
        $allPermissions = $this->buildPermissions();

        foreach ($allPermissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        // Hapus permission lama yang tidak ada di config lagi
        Permission::whereNotIn('name', $allPermissions)->delete();

        // ── Role: user — tidak punya permission default ──────────────
        Role::firstOrCreate(['name' => 'user'])
            ->syncPermissions([]);

        // ── Role: admin — semua kecuali delete ───────────────────────
        Role::firstOrCreate(['name' => 'admin'])
            ->syncPermissions(
                collect($allPermissions)
                    ->reject(fn ($p) => str_ends_with($p, '.delete'))
                    ->values()
                    ->all()
            );

        // ── Role: superadmin — semua permission ──────────────────────
        Role::firstOrCreate(['name' => 'superadmin'])
            ->syncPermissions($allPermissions);
    }

    /** Flatten config/modules.php → ['users.view', 'users.create', ...] */
    private function buildPermissions(): array
    {
        $permissions = [];

        foreach (config('modules', []) as $module => $actions) {
            foreach ($actions as $action) {
                $permissions[] = "{$module}.{$action}";
            }
        }

        return $permissions;
    }
}
