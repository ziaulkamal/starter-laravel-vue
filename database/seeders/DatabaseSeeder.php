<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Mtq\MtqMasterSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\WilayahSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            MenuSeeder::class,
            ProfileMenuSeeder::class,
            WilayahSeeder::class,
            MtqMasterSeeder::class,
        ]);

        $users = [
            ['name' => 'Super Admin', 'email' => 'superadmin@demo.com', 'role' => 'superadmin'],
            ['name' => 'Admin',       'email' => 'admin@demo.com',      'role' => 'admin'],
            ['name' => 'User',        'email' => 'user@demo.com',        'role' => 'user'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'      => $data['name'],
                    'password'  => 'password',
                    'is_active' => true,
                ]
            );
            $user->syncRoles($data['role']);
        }
    }
}
