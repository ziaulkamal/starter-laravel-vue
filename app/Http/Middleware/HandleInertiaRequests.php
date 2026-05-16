<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use App\Models\ProfileMenuItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user            = $request->user() instanceof User ? $request->user() : null;
        $userRoles       = $user ? $user->getRoleNames()->toArray() : [];
        $userPermissions = $user ? $user->getAllPermissions()->pluck('name')->toArray() : [];
        $isSuperAdmin    = in_array('superadmin', $userRoles);

        return [
            ...parent::share($request),

            'appName' => config('app.name'),

            'auth' => [
                'user' => $user ? [
                    'id'           => $user->id,
                    'name'         => $user->name,
                    'email'        => $user->email,
                    'avatar'       => $user->avatar,
                    'login_method' => $user->login_method ?? 'password',
                    'is_active'    => (bool) $user->is_active,
                ] : null,
                'permissions' => $userPermissions,
                'roles'       => $userRoles,
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],

            'menu'         => $this->sharedMenu($userRoles, $userPermissions, $isSuperAdmin),
            'profile_menu' => $this->sharedProfileMenu(),
        ];
    }

    /** @param array<string> $userRoles @param array<string> $userPermissions */
    private function sharedMenu(array $userRoles, array $userPermissions, bool $isSuperAdmin): array
    {
        /** @var array<int, array<string, mixed>> $allMenus */
        $allMenus = Cache::rememberForever('app.menu', function () {
            return Menu::with([
                'children' => fn ($q) => $q->orderBy('order_index')->with('roles:id,name'),
                'roles:id,name',
            ])
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('order_index')
                ->get()
                ->map(fn (Menu $item) => [
                    'type'       => $item->type,
                    'label'      => $item->label,
                    'icon'       => $item->icon,
                    'href'       => $item->href,
                    'permission' => $item->permission,
                    'roles'      => $item->roles->pluck('name')->toArray(),
                    'children'   => $item->children->map(fn (Menu $child) => [
                        'type'       => $child->type,
                        'label'      => $child->label,
                        'href'       => $child->href,
                        'permission' => $child->permission,
                        'roles'      => $child->roles->pluck('name')->toArray(),
                    ])->toArray(),
                ])
                ->toArray();
        });

        $filtered = collect($allMenus)
            ->filter(fn (array $item) => $this->canSeeMenu($item, $userRoles, $userPermissions, $isSuperAdmin))
            ->map(fn (array $item) => $this->formatEntry($item, $userRoles, $userPermissions, $isSuperAdmin))
            ->values()
            ->toArray();

        return $this->stripEmptySections($filtered);
    }

    private function stripEmptySections(array $items): array
    {
        $result         = [];
        $pendingSection = null;

        foreach ($items as $item) {
            if ($item['type'] === 'section') {
                $pendingSection = $item;
            } else {
                if ($pendingSection !== null) {
                    $result[]       = $pendingSection;
                    $pendingSection = null;
                }
                $result[] = $item;
            }
        }

        return $result;
    }

    /** @param array<string, mixed> $menu @param array<string> $userRoles @param array<string> $userPermissions */
    private function canSeeMenu(array $menu, array $userRoles, array $userPermissions, bool $isSuperAdmin): bool
    {
        if ($isSuperAdmin) return true;

        if ($menu['permission'] !== null) {
            return in_array($menu['permission'], $userPermissions);
        }

        if (empty($menu['roles'])) return true;

        return !empty(array_intersect($menu['roles'], $userRoles));
    }

    /** @param array<string, mixed> $item @param array<string> $userRoles @param array<string> $userPermissions */
    private function formatEntry(array $item, array $userRoles, array $userPermissions, bool $isSuperAdmin): array
    {
        $entry = [
            'type'  => $item['type'],
            'label' => $item['label'],
        ];

        if ($item['type'] === 'item') {
            $entry['icon'] = $item['icon'];
            $entry['href'] = $item['href'];

            $visibleChildren = collect($item['children'])
                ->filter(fn (array $child) => $this->canSeeMenu($child, $userRoles, $userPermissions, $isSuperAdmin));

            if ($visibleChildren->isNotEmpty()) {
                $entry['children'] = $visibleChildren
                    ->map(fn (array $child) => [
                        'label' => $child['label'],
                        'href'  => $child['href'],
                    ])
                    ->values()
                    ->toArray();
            }
        }

        return $entry;
    }

    private function sharedProfileMenu(): array
    {
        return Cache::rememberForever('app.profile_menu', function () {
            return ProfileMenuItem::query()->where('is_active', true)
                ->orderBy('order_index')
                ->get()
                ->map(fn (ProfileMenuItem $item) => [
                    'label' => $item->label,
                    'icon'  => $item->icon,
                    'href'  => $item->href,
                ])
                ->toArray();
        });
    }
}
