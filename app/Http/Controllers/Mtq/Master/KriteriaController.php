<?php

namespace App\Http\Controllers\Mtq\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mtq\Master\StoreKriteriaRequest;
use App\Http\Requests\Mtq\Master\UpdateKriteriaRequest;
use App\Models\Cabang;
use App\Models\Kriteria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KriteriaController extends Controller
{
    public function index(): Response
    {
        $kriterias = Kriteria::with('cabang')
            ->orderBy('cabang_id')
            ->orderBy('nama')
            ->get()
            ->map(fn(Kriteria $k) => [
                'id'          => $k->id,
                'cabang_id'   => $k->cabang_id,
                'cabang_nama' => $k->cabang->nama,
                'nama'        => $k->nama,
                'bobot'       => $k->bobot,
                'min_nilai'   => $k->min_nilai,
                'max_nilai'   => $k->max_nilai,
            ]);

        return Inertia::render('Mtq/Master/Kriteria/Index', [
            'kriterias' => $kriterias,
            'cabangs'   => Cabang::where('is_active', true)->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(StoreKriteriaRequest $request): RedirectResponse
    {
        $kriteria = Kriteria::create($request->validated());

        return back()->with('success', "Kriteria {$kriteria->nama} berhasil ditambahkan.");
    }

    public function update(UpdateKriteriaRequest $request, Kriteria $kriteria): RedirectResponse
    {
        $kriteria->update($request->validated());

        return back()->with('success', "Kriteria {$kriteria->nama} berhasil diperbarui.");
    }

    public function destroy(Kriteria $kriteria): RedirectResponse
    {
        $nama = $kriteria->nama;
        $kriteria->delete();

        return back()->with('success', "Kriteria {$nama} berhasil dihapus.");
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];
        Kriteria::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' kriteria berhasil dihapus.');
    }
}
