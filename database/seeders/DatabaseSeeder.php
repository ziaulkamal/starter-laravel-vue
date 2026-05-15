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

        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name'      => 'Admin',
                'password'  => 'admin',
                'is_active' => true,
            ]
        );

        $admin->syncRoles('superadmin');
    }
}
