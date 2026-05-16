<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;

class PermissionScanController extends Controller
{
    public function scan(): JsonResponse
    {
        $existing = Permission::pluck('name')->flip()->toArray();

        $permMap = [];

        foreach (Route::getRoutes() as $route) {
            foreach ($route->middleware() as $mw) {
                if (! str_starts_with($mw, 'permission:')) {
                    continue;
                }

                $permStr = substr($mw, strlen('permission:'));

                foreach (explode('|', $permStr) as $perm) {
                    $perm = trim($perm);
                    if (! $perm || isset($permMap[$perm])) {
                        continue;
                    }

                    $permMap[$perm] = [
                        'name'   => $perm,
                        'route'  => $route->getName(),
                        'method' => implode('|', array_diff($route->methods(), ['HEAD'])),
                        'uri'    => '/' . ltrim($route->uri(), '/'),
                        'exists' => isset($existing[$perm]),
                    ];
                }
            }
        }

        $modules = [];
        foreach ($permMap as $perm => $data) {
            $module = explode('.', $perm)[0];

            if (! isset($modules[$module])) {
                $modules[$module] = [
                    'name'        => $module,
                    'permissions' => [],
                    'all_exist'   => true,
                    'some_exist'  => false,
                ];
            }

            $modules[$module]['permissions'][] = $data;

            if ($data['exists']) {
                $modules[$module]['some_exist'] = true;
            } else {
                $modules[$module]['all_exist'] = false;
            }
        }

        // Sort: new first, then partial, then fully synced
        usort($modules, fn ($a, $b) => $a['all_exist'] <=> $b['all_exist'] ?: $b['some_exist'] <=> $a['some_exist']);

        return response()->json([
            'modules'      => array_values($modules),
            'total_routes' => count(Route::getRoutes()),
            'scanned_at'   => now()->format('d M Y H:i:s'),
        ]);
    }

    public function sync(Request $request): JsonResponse
    {
        $data = $request->validate([
            'permissions'   => ['required', 'array'],
            'permissions.*' => ['required', 'string', 'max:100'],
        ]);

        $created = [];
        foreach ($data['permissions'] as $name) {
            if (! Permission::where('name', $name)->exists()) {
                Permission::create(['name' => $name, 'guard_name' => 'web']);
                $created[] = $name;
            }
        }

        return response()->json([
            'created' => $created,
            'count'   => count($created),
        ]);
    }
}
