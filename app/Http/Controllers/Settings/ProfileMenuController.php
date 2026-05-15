<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreProfileMenuRequest;
use App\Http\Requests\Settings\UpdateProfileMenuRequest;
use App\Models\ProfileMenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class ProfileMenuController extends Controller
{
    public function index(): Response
    {
        $items = ProfileMenuItem::orderBy('order_index', 'asc')->get();

        $usedHrefs = $items->pluck('href')->filter()->values()->toArray();

        return Inertia::render('Settings/ProfileMenu/Index', [
            'items'          => $items,
            'availableRoutes' => $this->getGetRoutes(),
            'usedHrefs'      => $usedHrefs,
        ]);
    }

    public function store(StoreProfileMenuRequest $request): RedirectResponse
    {
        ProfileMenuItem::create($request->validated());

        $this->clearCache();

        return back()->with('success', 'Item menu profil berhasil ditambahkan.');
    }

    public function update(UpdateProfileMenuRequest $request, ProfileMenuItem $profileMenuItem): RedirectResponse
    {
        $profileMenuItem->update($request->validated());

        $this->clearCache();

        return back()->with('success', 'Item menu profil berhasil diperbarui.');
    }

    public function destroy(ProfileMenuItem $profileMenuItem): RedirectResponse
    {
        $profileMenuItem->forceDelete();

        $this->clearCache();

        return back()->with('success', 'Item menu profil berhasil dihapus.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'orders'         => ['required', 'array'],
            'orders.*.id'    => ['required', 'integer', 'exists:profile_menu_items,id'],
            'orders.*.index' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($request->orders as $item) {
            ProfileMenuItem::query()->whereKey($item['id'])->update(['order_index' => $item['index']]);
        }

        $this->clearCache();

        return back()->with('success', 'Urutan berhasil disimpan.');
    }

    public function destroyAll(): RedirectResponse
    {
        ProfileMenuItem::query()->delete();

        $this->clearCache();

        return back()->with('success', 'Semua item menu profil berhasil dihapus.');
    }

    private function clearCache(): void
    {
        Cache::forget('app.profile_menu');
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
}
