<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            WilayahSeeder::class,
            RolePermissionSeeder::class,
            MenuSeeder::class,
            ProfileMenuSeeder::class,
            SystemConfigSeeder::class,
            SubstansiKategoriSeeder::class,
        ]);

        // Cari kode desa pertama di Blangpidie untuk demo admin_gampong
        $kodeDesaDemo = \DB::table('wilayah')
            ->where('kode', 'like', '11.12.01.%')
            ->whereRaw('LENGTH(kode) = 13')
            ->value('kode');

        // Demo users — 1 super_admin, 1 admin_kabupaten, 1 admin_gampong
        $users = [
            [
                'name'         => 'Super Admin',
                'email'        => 'superadmin@baitul-mal.test',
                'role'         => 'super_admin',
                'kode_wilayah' => null,
            ],
            [
                'name'         => 'Admin Kabupaten',
                'email'        => 'admin@baitul-mal.test',
                'role'         => 'admin_kabupaten',
                'kode_wilayah' => null,
            ],
            [
                'name'         => 'Admin Gampong Demo',
                'email'        => 'gampong@baitul-mal.test',
                'role'         => 'admin_gampong',
                'kode_wilayah' => $kodeDesaDemo, // null jika wilayah belum diimport
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'         => $data['name'],
                    'password'     => 'password',
                    'is_active'    => true,
                    'kode_wilayah' => $data['kode_wilayah'],
                ]
            );
            $user->syncRoles($data['role']);
        }
    }
}
