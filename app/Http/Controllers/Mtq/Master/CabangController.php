<?php

namespace App\Http\Controllers\Mtq\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mtq\Master\StoreCabangRequest;
use App\Http\Requests\Mtq\Master\UpdateCabangRequest;
use App\Models\Cabang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CabangController extends Controller
{
    public function index(): Response
    {
        $cabangs = Cabang::orderBy('nama')
            ->get()
            ->map(fn(Cabang $c) => [
                'id'        => $c->id,
                'nama'      => $c->nama,
                'deskripsi' => $c->deskripsi,
                'is_active' => $c->is_active,
            ]);

        return Inertia::render('Mtq/Master/Cabang/Index', [
            'cabangs' => $cabangs,
        ]);
    }

    public function store(StoreCabangRequest $request): RedirectResponse
    {
        $cabang = Cabang::create($request->validated());

        return back()->with('success', "Cabang {$cabang->nama} berhasil ditambahkan.");
    }

    public function update(UpdateCabangRequest $request, Cabang $cabang): RedirectResponse
    {
        $cabang->update($request->validated());

        return back()->with('success', "Cabang {$cabang->nama} berhasil diperbarui.");
    }

    public function destroy(Cabang $cabang): RedirectResponse
    {
        $nama = $cabang->nama;
        $cabang->delete();

        return back()->with('success', "Cabang {$nama} berhasil dihapus.");
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];
        Cabang::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' cabang berhasil dihapus.');
    }
}
