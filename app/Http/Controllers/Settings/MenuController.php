<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreMenuRequest;
use App\Http\Requests\Settings\UpdateMenuRequest;
use App\Models\Menu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class MenuController extends Controller
{
    public function index(): Response
    {
        $menus = Menu::with(['children' => fn ($q) => $q->orderBy('order_index')])
            ->roots()
            ->orderBy('order_index')
            ->get();

        $usedHrefs = $menus
            ->flatMap(fn (Menu $m) => collect([$m->href])->concat($m->children->pluck('href')))
            ->filter()
            ->values()
            ->toArray();

        return Inertia::render('Settings/Menu/Index', [
            'menus'           => $menus,
            'availableRoutes' => $this->getGetRoutes(),
            'usedHrefs'       => $usedHrefs,
        ]);
    }

    private function getGetRoutes(): array
    {
        return collect(app('router')->getRoutes()->getRoutesByMethod()['GET'] ?? [])
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

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        Menu::create($request->validated());

        $this->clearMenuCache();

        return back()->with('success', 'Menu berhasil ditambahkan.');
    }

    public function update(UpdateMenuRequest $request, Menu $menu): RedirectResponse
    {
        $menu->update($request->validated());

        $this->clearMenuCache();

        return back()->with('success', 'Menu berhasil diperbarui.');
    }

    public function destroy(Menu $menu): RedirectResponse
    {
        // Children otomatis ter-delete karena SET NULL on parent_id,
        // tapi kita hapus manual agar bersih
        $menu->children()->delete();
        $menu->delete();

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
            Menu::where('id', $item['id'])->update(['order_index' => $item['index']]);
        }

        $this->clearMenuCache();

        return back()->with('success', 'Urutan menu berhasil disimpan.');
    }

    private function clearMenuCache(): void
    {
        Cache::forget('app.menu');
    }
}
