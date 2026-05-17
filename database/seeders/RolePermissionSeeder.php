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

        $allPermissions = $this->buildPermissions();

        foreach ($allPermissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        Permission::whereNotIn('name', $allPermissions)->delete();

        // ── super_admin: semua permission ────────────────────────────
        Role::firstOrCreate(['name' => 'super_admin'])
            ->syncPermissions($allPermissions);

        // ── admin_kabupaten: kelola mustahik, pengajuan, verifikasi,
        //    zakat monitor, muzakki, program, penyaluran, laporan ─────
        $adminKabPermissions = collect($allPermissions)->filter(
            fn ($p) => str_starts_with($p, 'mustahik.') ||
                       str_starts_with($p, 'berkas.') ||
                       str_starts_with($p, 'pengajuan.') ||
                       str_starts_with($p, 'verifikasi.') ||
                       str_starts_with($p, 'zakat-monitor.') ||
                       str_starts_with($p, 'muzakki.') ||
                       str_starts_with($p, 'program.') ||
                       str_starts_with($p, 'penyaluran.') ||
                       str_starts_with($p, 'laporan.') ||
                       str_starts_with($p, 'users.view')
        )->values()->all();

        Role::firstOrCreate(['name' => 'admin_kabupaten'])
            ->syncPermissions($adminKabPermissions);

        // ── admin_gampong: mustahik gampong sendiri, pengajuan,
        //    zakat fitrah, zakat mal, muzakki, laporan ─────────────
        $adminGampongPermissions = collect($allPermissions)->filter(
            fn ($p) => in_array($p, [
                'mustahik.view', 'mustahik.create', 'mustahik.edit',
                'berkas.upload', 'berkas.download',
                'pengajuan.view', 'pengajuan.create',
                'zakat-fitrah.view', 'zakat-fitrah.create', 'zakat-fitrah.delete', 'zakat-fitrah.kunci',
                'zakat-mal.view', 'zakat-mal.create', 'zakat-mal.delete',
                'muzakki.view', 'muzakki.create', 'muzakki.edit',
                'laporan.view', 'laporan.export',
            ])
        )->values()->all();

        Role::firstOrCreate(['name' => 'admin_gampong'])
            ->syncPermissions($adminGampongPermissions);
    }

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
