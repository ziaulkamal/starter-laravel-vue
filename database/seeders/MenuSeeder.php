<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('menu_role')->truncate();
        DB::table('menus')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $superadmin = Role::where('name', 'superadmin')->first();

        $items = [
            // ── Home ─────────────────────────────────────────────
            [
                'type'        => 'section',
                'label'       => 'Main Menu',
                'order_index' => 0,
            ],
            [
                'type'        => 'item',
                'label'       => 'Dashboard',
                'icon'        => 'ti ti-layout-dashboard',
                'href'        => '/',
                'order_index' => 1,
                // No restriction — visible to all authenticated users
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
                'href'        => '/settings/users',
                'permission'  => 'users.view',
                'order_index' => 3,
            ],
            [
                'type'        => 'item',
                'label'       => 'Roles & Permissions',
                'icon'        => 'ti ti-shield-lock',
                'href'        => '/settings/roles',
                'order_index' => 4,
                'roles'       => [$superadmin?->id],
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
                // No restriction — visible to all
            ],

            // ── Pengaturan ────────────────────────────────────────
            [
                'type'        => 'section',
                'label'       => 'Pengaturan',
                'order_index' => 7,
            ],
            [
                'type'        => 'item',
                'label'       => 'Pengaturan Menu',
                'icon'        => 'ti ti-menu-2',
                'href'        => '/settings/menus',
                'order_index' => 8,
                'roles'       => [$superadmin?->id],
                'children'    => [
                    ['label' => 'Menu Sidebar', 'href' => '/settings/menus',        'order_index' => 0],
                    ['label' => 'Menu Profil',  'href' => '/settings/profile-menu', 'order_index' => 1],
                ],
            ],
        ];

        foreach ($items as $item) {
            $children = $item['children'] ?? [];
            $roleIds  = array_filter($item['roles'] ?? []);
            unset($item['children'], $item['roles']);

            $parent = Menu::create($item);

            if (!empty($roleIds)) {
                $parent->roles()->sync($roleIds);
            }

            foreach ($children as $child) {
                $childRoleIds = array_filter($child['roles'] ?? $roleIds);
                $menu = Menu::create([
                    'parent_id'   => $parent->id,
                    'type'        => 'item',
                    'label'       => $child['label'],
                    'href'        => $child['href'],
                    'permission'  => $child['permission'] ?? null,
                    'order_index' => $child['order_index'],
                ]);

                if (!empty($childRoleIds)) {
                    $menu->roles()->sync($childRoleIds);
                }
            }
        }
    }
}
