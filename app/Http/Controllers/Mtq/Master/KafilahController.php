<?php

namespace App\Http\Controllers\Mtq\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mtq\Master\StoreKafilahRequest;
use App\Http\Requests\Mtq\Master\UpdateKafilahRequest;
use App\Models\Kafilah;
use App\Models\Wilayah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KafilahController extends Controller
{
    public function index(): Response
    {
        $kafilahs = Kafilah::orderBy('nama_kabupaten')
            ->get()
            ->map(fn(Kafilah $k) => [
                'id'                  => $k->id,
                'nama_kabupaten'      => $k->nama_kabupaten,
                'kode_wilayah'        => $k->kode_wilayah,
                'koordinator_nama'    => $k->koordinator_nama,
                'koordinator_kontak'  => $k->koordinator_kontak,
            ]);

        $wilayahAceh = Wilayah::kabupaten('11')
            ->get(['kode', 'nama'])
            ->map(fn(Wilayah $w) => ['value' => $w->kode, 'label' => $w->nama])
            ->values();

        return Inertia::render('Mtq/Master/Kafilah/Index', [
            'kafilahs'    => $kafilahs,
            'wilayahAceh' => $wilayahAceh,
        ]);
    }

    public function store(StoreKafilahRequest $request): RedirectResponse
    {
        $kafilah = Kafilah::create($request->validated());

        return back()->with('success', "Kafilah {$kafilah->nama_kabupaten} berhasil ditambahkan.");
    }

    public function update(UpdateKafilahRequest $request, Kafilah $kafilah): RedirectResponse
    {
        $kafilah->update($request->validated());

        return back()->with('success', "Kafilah {$kafilah->nama_kabupaten} berhasil diperbarui.");
    }

    public function destroy(Kafilah $kafilah): RedirectResponse
    {
        $nama = $kafilah->nama_kabupaten;
        $kafilah->delete();

        return back()->with('success', "Kafilah {$nama} berhasil dihapus.");
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];
        Kafilah::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' kafilah berhasil dihapus.');
    }
}
