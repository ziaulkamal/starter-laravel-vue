<?php

namespace App\Http\Controllers\Mtq\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mtq\Master\UpdateKonfigurasiRequest;
use App\Models\KonfigurasiEvent;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class KonfigurasiController extends Controller
{
    public function show(): Response
    {
        $konfigurasi = KonfigurasiEvent::first();

        return Inertia::render('Mtq/Master/Konfigurasi/Index', [
            'konfigurasi' => $konfigurasi ? [
                'id'               => $konfigurasi->id,
                'tahun'            => $konfigurasi->tahun,
                'tema'             => $konfigurasi->tema,
                'tgl_mulai_daftar' => $konfigurasi->tgl_mulai_daftar?->format('Y-m-d'),
                'tgl_tutup_daftar' => $konfigurasi->tgl_tutup_daftar?->format('Y-m-d'),
                'tgl_pelaksanaan'  => $konfigurasi->tgl_pelaksanaan?->format('Y-m-d'),
            ] : null,
        ]);
    }

    public function update(UpdateKonfigurasiRequest $request): RedirectResponse
    {
        KonfigurasiEvent::updateOrCreate(['id' => 1], $request->validated());

        return back()->with('success', 'Konfigurasi event berhasil disimpan.');
    }
}
