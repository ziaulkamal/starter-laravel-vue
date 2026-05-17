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
        $admin      = Role::where('name', 'admin')->first();
        $user       = Role::where('name', 'user')->first();

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
            ],

            // ── SIM-MTQ ──────────────────────────────────────────
            [
                'type'        => 'section',
                'label'       => 'SIM-MTQ',
                'order_index' => 7,
            ],
            [
                'type'        => 'item',
                'label'       => 'Master Data',
                'icon'        => 'ti ti-database',
                'href'        => '/mtq/master/cabang',
                'order_index' => 8,
                'roles'       => [$superadmin?->id],
                'children'    => [
                    ['label' => 'Cabang Lomba',  'href' => '/mtq/master/cabang',       'order_index' => 0],
                    ['label' => 'Golongan',       'href' => '/mtq/master/golongan',     'order_index' => 1],
                    ['label' => 'Kriteria',       'href' => '/mtq/master/kriteria',     'order_index' => 2],
                    ['label' => 'Kafilah',        'href' => '/mtq/master/kafilah',      'order_index' => 3],
                    ['label' => 'Venue',          'href' => '/mtq/master/venue',        'order_index' => 4],
                    ['label' => 'Konfigurasi',    'href' => '/mtq/master/konfigurasi',  'order_index' => 5],
                ],
            ],
            [
                'type'        => 'item',
                'label'       => 'Pendaftaran Peserta',
                'icon'        => 'ti ti-user-plus',
                'href'        => '/mtq/peserta',
                'order_index' => 9,
            ],
            // Pengajuan Edit — hanya untuk role user (petugas kafilah)
            [
                'type'        => 'item',
                'label'       => 'Pengajuan Edit',
                'icon'        => 'ti ti-edit-circle',
                'href'        => '/mtq/pengajuan-edit',
                'order_index' => 10,
                'roles'       => array_filter([$user?->id]),
            ],
            // Manajemen Pengajuan Edit — untuk admin dan superadmin
            [
                'type'        => 'item',
                'label'       => 'Manajemen Pengajuan',
                'icon'        => 'ti ti-clipboard-check',
                'href'        => '/mtq/pengajuan-edit/manage',
                'order_index' => 11,
                'roles'       => array_filter([$admin?->id, $superadmin?->id]),
            ],
            // Manajemen Pengajuan Hapus — hanya superadmin
            [
                'type'        => 'item',
                'label'       => 'Pengajuan Hapus',
                'icon'        => 'ti ti-trash-x',
                'href'        => '/mtq/pengajuan-hapus/manage',
                'order_index' => 12,
                'roles'       => array_filter([$superadmin?->id]),
            ],

            // ── Pengaturan ────────────────────────────────────────
            [
                'type'        => 'section',
                'label'       => 'Pengaturan',
                'order_index' => 13,
            ],
            [
                'type'        => 'item',
                'label'       => 'Pengaturan Menu',
                'icon'        => 'ti ti-menu-2',
                'href'        => '/settings/menus',
                'order_index' => 14,
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
