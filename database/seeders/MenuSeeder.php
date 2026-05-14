<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Data diambil dari resources/js/config/menu.ts sebagai seed awal.
        // Setelah seed ini dijalankan, menu dikelola sepenuhnya via database.
        $items = [
            // ── Home ─────────────────────────────────────────────
            [
                'type'        => 'section',
                'label'       => 'Home',
                'order_index' => 0,
            ],
            [
                'type'        => 'item',
                'label'       => 'Dashboard',
                'icon'        => 'ti ti-layout-dashboard',
                'href'        => '/',
                'order_index' => 1,
            ],

            // ── Management ───────────────────────────────────────
            [
                'type'        => 'section',
                'label'       => 'Management',
                'order_index' => 2,
            ],
            [
                'type'        => 'item',
                'label'       => 'Users',
                'icon'        => 'ti ti-users',
                'href'        => '/users',
                'order_index' => 3,
                'children'    => [
                    ['label' => 'User List',   'href' => '/users',        'order_index' => 0],
                    ['label' => 'Create User', 'href' => '/users/create', 'order_index' => 1],
                ],
            ],
            [
                'type'        => 'item',
                'label'       => 'Roles & Permissions',
                'icon'        => 'ti ti-shield-lock',
                'href'        => '/roles',
                'order_index' => 4,
                'children'    => [
                    ['label' => 'Role List',    'href' => '/roles',       'order_index' => 0],
                    ['label' => 'Permissions',  'href' => '/permissions', 'order_index' => 1],
                ],
            ],

            // ── Pages ────────────────────────────────────────────
            [
                'type'        => 'section',
                'label'       => 'Pages',
                'order_index' => 5,
            ],
            [
                'type'        => 'item',
                'label'       => 'Profile',
                'icon'        => 'ti ti-user-circle',
                'href'        => '/profile',
                'order_index' => 6,
            ],
            [
                'type'        => 'item',
                'label'       => 'Settings',
                'icon'        => 'ti ti-settings',
                'href'        => '/settings',
                'order_index' => 7,
            ],
        ];

        foreach ($items as $item) {
            $children = $item['children'] ?? [];
            unset($item['children']);

            $parent = Menu::create($item);

            foreach ($children as $child) {
                Menu::create([
                    'parent_id'   => $parent->id,
                    'type'        => 'item',
                    'label'       => $child['label'],
                    'href'        => $child['href'],
                    'order_index' => $child['order_index'],
                ]);
            }
        }
    }
}
