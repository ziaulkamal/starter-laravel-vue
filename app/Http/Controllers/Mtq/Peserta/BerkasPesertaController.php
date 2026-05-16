<?php

namespace App\Http\Controllers\Mtq\Peserta;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mtq\Peserta\StoreBerkasRequest;
use App\Models\BerkasPeserta;
use App\Models\Peserta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class BerkasPesertaController extends Controller
{
    public function store(StoreBerkasRequest $request, Peserta $peserta): RedirectResponse
    {
        $file = $request->file('file');
        $path = $file->store("peserta/{$peserta->id}/berkas", 'public');
        BerkasPeserta::create([
            'peserta_id' => $peserta->id,
            'jenis'      => $request->validated()['jenis'],
            'path'       => $path,
            'nama_asli'  => $file->getClientOriginalName(),
            'mime_type'  => $file->getMimeType(),
            'ukuran'     => $file->getSize(),
        ]);
        return back()->with('success', 'Berkas berhasil diunggah.');
    }

    public function destroy(BerkasPeserta $berkas): RedirectResponse
    {
        Storage::disk('public')->delete($berkas->path);
        $berkas->delete();
        return back()->with('success', 'Berkas berhasil dihapus.');
    }
}
