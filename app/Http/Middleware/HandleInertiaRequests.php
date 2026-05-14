<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use App\Models\ProfileMenuItem;
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
        return [
            ...parent::share($request),

            'appName' => config('app.name'),

            'auth' => [
                'user' => $request->user()?->only('id', 'name', 'email'),
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],

            'menu'         => $this->sharedMenu(),
            'profile_menu' => $this->sharedProfileMenu(),
        ];
    }

    private function sharedMenu(): array
    {
        return Cache::rememberForever('app.menu', function () {
            return Menu::with(['children' => fn ($q) => $q->orderBy('order_index')])
                ->active()
                ->roots()
                ->orderBy('order_index')
                ->get()
                ->map(fn (Menu $item) => $this->formatEntry($item))
                ->toArray();
        });
    }

    private function formatEntry(Menu $item): array
    {
        $entry = [
            'type'  => $item->type,
            'label' => $item->label,
        ];

        if ($item->type === 'item') {
            $entry['icon'] = $item->icon;
            $entry['href'] = $item->href;

            if ($item->children->isNotEmpty()) {
                $entry['children'] = $item->children
                    ->map(fn (Menu $child) => [
                        'label' => $child->label,
                        'href'  => $child->href,
                    ])
                    ->toArray();
            }
        }

        return $entry;
    }

    private function sharedProfileMenu(): array
    {
        return Cache::rememberForever('app.profile_menu', function () {
            return ProfileMenuItem::active()
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
