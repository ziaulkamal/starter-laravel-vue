<?php

namespace App\Http\Controllers\Mtq\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mtq\Master\StoreGolonganRequest;
use App\Http\Requests\Mtq\Master\UpdateGolonganRequest;
use App\Models\Cabang;
use App\Models\Golongan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GolonganController extends Controller
{
    public function index(): Response
    {
        $golongans = Golongan::with('cabang')
            ->orderBy('cabang_id')
            ->orderBy('nama')
            ->get()
            ->map(fn(Golongan $g) => [
                'id'             => $g->id,
                'cabang_id'      => $g->cabang_id,
                'cabang_nama'    => $g->cabang->nama,
                'nama'           => $g->nama,
                'min_usia'       => $g->min_usia,
                'max_usia'       => $g->max_usia,
                'jenis_kelamin'  => $g->jenis_kelamin,
            ]);

        return Inertia::render('Mtq/Master/Golongan/Index', [
            'golongans' => $golongans,
            'cabangs'   => Cabang::where('is_active', true)->orderBy('nama')->get(['id', 'nama']),
        ]);
    }

    public function store(StoreGolonganRequest $request): RedirectResponse
    {
        $golongan = Golongan::create($request->validated());

        return back()->with('success', "Golongan {$golongan->nama} berhasil ditambahkan.");
    }

    public function update(UpdateGolonganRequest $request, Golongan $golongan): RedirectResponse
    {
        $golongan->update($request->validated());

        return back()->with('success', "Golongan {$golongan->nama} berhasil diperbarui.");
    }

    public function destroy(Golongan $golongan): RedirectResponse
    {
        $nama = $golongan->nama;
        $golongan->delete();

        return back()->with('success', "Golongan {$nama} berhasil dihapus.");
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];
        Golongan::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' golongan berhasil dihapus.');
    }
}
