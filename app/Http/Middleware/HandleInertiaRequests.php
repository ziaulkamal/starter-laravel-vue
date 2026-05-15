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
        $user      = $request->user() instanceof User ? $request->user() : null;
        $userRoles = $user ? $user->getRoleNames()->toArray() : [];
        $isSuperAdmin = in_array('superadmin', $userRoles);

        return [
            ...parent::share($request),

            'appName' => config('app.name'),

            'auth' => [
                'user'        => $user?->only('id', 'name', 'email'),
                'permissions' => $user?->getAllPermissions()->pluck('name')->toArray(),
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],

            'menu'         => $this->sharedMenu($userRoles, $isSuperAdmin),
            'profile_menu' => $this->sharedProfileMenu(),
        ];
    }

    private function sharedMenu(array $userRoles, bool $isSuperAdmin): array
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
                    'type'     => $item->type,
                    'label'    => $item->label,
                    'icon'     => $item->icon,
                    'href'     => $item->href,
                    'roles'    => $item->roles->pluck('name')->toArray(),
                    'children' => $item->children->map(fn (Menu $child) => [
                        'type'  => $child->type,
                        'label' => $child->label,
                        'href'  => $child->href,
                        'roles' => $child->roles->pluck('name')->toArray(),
                    ])->toArray(),
                ])
                ->toArray();
        });

        return collect($allMenus)
            ->filter(fn (array $item) => $this->canSeeMenu($item['roles'], $userRoles, $isSuperAdmin))
            ->map(fn (array $item) => $this->formatEntry($item, $userRoles, $isSuperAdmin))
            ->values()
            ->toArray();
    }

    private function canSeeMenu(array $menuRoles, array $userRoles, bool $isSuperAdmin): bool
    {
        if ($isSuperAdmin) return true;
        if (empty($menuRoles)) return true;

        return !empty(array_intersect($menuRoles, $userRoles));
    }

    private function formatEntry(array $item, array $userRoles, bool $isSuperAdmin): array
    {
        $entry = [
            'type'  => $item['type'],
            'label' => $item['label'],
        ];

        if ($item['type'] === 'item') {
            $entry['icon'] = $item['icon'];
            $entry['href'] = $item['href'];

            $visibleChildren = collect($item['children'])
                ->filter(fn (array $child) => $this->canSeeMenu($child['roles'], $userRoles, $isSuperAdmin));

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
