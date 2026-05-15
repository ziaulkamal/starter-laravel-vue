<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

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
