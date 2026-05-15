<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreMenuRequest;
use App\Http\Requests\Settings\UpdateMenuRequest;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MenuController extends Controller
{
    public function index(): Response
    {
        $menus = Menu::with([
            'children' => fn ($q) => $q->orderBy('order_index')->with('roles:id,name'),
            'roles:id,name',
        ])
            ->whereNull('parent_id')
            ->orderBy('order_index')
            ->get();

        $usedHrefs = $menus
            ->flatMap(fn (Menu $m) => collect([$m->href])->concat($m->children->pluck('href')))
            ->filter()
            ->values()
            ->toArray();

        return Inertia::render('Settings/Menu/Index', [
            'menus'               => $menus,
            'availableRoutes'     => $this->getGetRoutes(),
            'usedHrefs'           => $usedHrefs,
            'roles'               => Role::orderBy('name')->get(['id', 'name']),
            'availablePermissions' => Permission::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $data    = $request->validated();
        $roleIds = $data['role_ids'] ?? [];
        unset($data['role_ids']);

        $menu = Menu::create($data);
        $menu->roles()->sync($roleIds);

        $this->clearMenuCache();

        return back()->with('success', 'Menu berhasil ditambahkan.');
    }

    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $data    = $request->validated();
        $roleIds = $data['role_ids'] ?? [];
        unset($data['role_ids']);

        $menu->update($data);
        $menu->roles()->sync($roleIds);

        $this->clearMenuCache();

        return back()->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        $menu->children()->delete();
        Menu::query()->whereKey($menu->id)->delete();

        $this->clearMenuCache();

        return back()->with('success', 'Menu berhasil dihapus.');
    }

    public function destroyAll(): RedirectResponse
    {
        Menu::query()->delete();

        $this->clearMenuCache();

        return back()->with('success', 'Semua menu berhasil dihapus.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'orders'         => ['required', 'array'],
            'orders.*.id'    => ['required', 'integer', 'exists:menus,id'],
            'orders.*.index' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->orders as $item) {
            Menu::query()->whereKey($item['id'])->update(['order_index' => $item['index']]);
        }

        $this->clearMenuCache();

        return back()->with('success', 'Urutan menu berhasil disimpan.');
    }

    private function getGetRoutes(): array
    {
        /** @var Router $router */
        $router = app('router');

        return collect($router->getRoutes()->getRoutesByMethod()['GET'] ?? [])
            ->filter(fn ($route) =>
                !str_contains($route->uri(), '{') &&
                !str_starts_with($route->uri(), '_ignition') &&
                !str_starts_with($route->uri(), 'sanctum') &&
                $route->uri() !== 'up'
            )
            ->map(fn ($route) => '/' . ltrim($route->uri(), '/'))
            ->unique()
            ->sort()
            ->values()
            ->toArray();
    }

    private function clearMenuCache(): void
    {
        Cache::forget('app.menu');
    }
}
