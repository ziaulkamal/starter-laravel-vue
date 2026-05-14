<?php

namespace Database\Seeders;

use App\Models\ProfileMenuItem;
use Illuminate\Database\Seeder;

class ProfileMenuSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'label'       => 'Profil Saya',
                'icon'        => 'ti ti-user-circle',
                'href'        => '/profile',
                'order_index' => 0,
            ],
            [
                'label'       => 'Pengaturan Menu',
                'icon'        => 'ti ti-menu-2',
                'href'        => '/settings/menus',
                'order_index' => 1,
            ],
        ];

        foreach ($items as $item) {
            ProfileMenuItem::create($item);
        }
    }
}
