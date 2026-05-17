<?php

namespace App\Http\Controllers\Gampong;

use App\Http\Controllers\Controller;
use App\Models\Mustahik;
use App\Models\PengajuanBantuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PengajuanBantuanController extends Controller
{
    public function store(Request $request, Mustahik $mustahik): RedirectResponse
    {
        $this->authorize('view', $mustahik);

        $data = $request->validate([
            'substansi_kategori_id' => ['required', 'integer', 'exists:substansi_kategori,id'],
            'catatan_pengaju'        => ['nullable', 'string', 'max:1000'],
        ]);

        $tahun = now()->year;

        $already = PengajuanBantuan::where('mustahik_id', $mustahik->id)
            ->where('tahun', $tahun)
            ->exists();

        if ($already) {
            return back()->with('error', 'Mustahik ini sudah memiliki pengajuan untuk tahun ' . $tahun . '.');
        }

        PengajuanBantuan::create([
            'mustahik_id'           => $mustahik->id,
            'substansi_kategori_id' => $data['substansi_kategori_id'],
            'kode_desa'             => $mustahik->kode_desa,
            'tahun'                 => $tahun,
            'diajukan_oleh'         => $request->user()->id,
            'status'                => 'diajukan',
            'catatan_pengaju'       => $data['catatan_pengaju'] ?? null,
            'tanggal_pengajuan'     => today(),
        ]);

        return back()->with('success', 'Pengajuan bantuan berhasil diajukan.');
    }

    public function sanggah(Request $request, PengajuanBantuan $pengajuan): RedirectResponse
    {
        $this->authorize('view', $pengajuan->mustahik);

        // Pastikan pengajuan ini milik gampong yang sama
        if ($pengajuan->kode_desa !== $request->user()->kode_wilayah) {
            abort(403);
        }

        if ($pengajuan->status !== 'ditolak') {
            return back()->with('error', 'Hanya pengajuan yang ditolak yang dapat disanggah.');
        }

        $data = $request->validate([
            'catatan_pengaju' => ['nullable', 'string', 'max:1000'],
        ]);

        $pengajuan->update([
            'status'          => 'sanggah',
            'catatan_pengaju' => $data['catatan_pengaju'] ?? $pengajuan->catatan_pengaju,
            'alasan_penolakan'=> null,
        ]);

        return back()->with('success', 'Sanggahan berhasil diajukan. Menunggu keputusan ulang.');
    }
}
