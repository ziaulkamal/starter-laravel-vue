<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubstansiKategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SenifController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Senif/Index', [
            'senif' => SubstansiKategori::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama'      => ['required', 'string', 'max:150'],
            'kode_asnaf'=> ['required', 'string', 'max:20', 'unique:substansi_kategori,kode_asnaf'],
            'keterangan'=> ['nullable', 'string', 'max:500'],
        ]);

        SubstansiKategori::create($data + ['is_active' => true]);

        return back()->with('success', "Senif '{$data['nama']}' berhasil ditambahkan.");
    }

    public function update(Request $request, SubstansiKategori $senif): RedirectResponse
    {
        $data = $request->validate([
            'nama'      => ['required', 'string', 'max:150'],
            'kode_asnaf'=> ['required', 'string', 'max:20', "unique:substansi_kategori,kode_asnaf,{$senif->id}"],
            'keterangan'=> ['nullable', 'string', 'max:500'],
        ]);

        $senif->update($data);

        return back()->with('success', "Senif '{$senif->nama}' berhasil diperbarui.");
    }

    public function toggle(SubstansiKategori $senif): RedirectResponse
    {
        $senif->update(['is_active' => ! $senif->is_active]);

        $label = $senif->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Senif '{$senif->nama}' berhasil {$label}.");
    }

    public function destroy(SubstansiKategori $senif): RedirectResponse
    {
        if ($senif->pengajuanBantuan()->exists()) {
            return back()->with('error', "Senif '{$senif->nama}' tidak dapat dihapus karena sudah digunakan dalam pengajuan.");
        }

        $nama = $senif->nama;
        $senif->delete();

        return back()->with('success', "Senif '{$nama}' berhasil dihapus.");
    }
}
