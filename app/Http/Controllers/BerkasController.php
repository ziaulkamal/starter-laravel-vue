<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadBerkasRequest;
use App\Models\BerkasMustahik;
use App\Models\Mustahik;
use App\Services\BerkasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BerkasController extends Controller
{
    public function __construct(private BerkasService $service) {}

    public function store(UploadBerkasRequest $request): JsonResponse
    {
        $mustahik = Mustahik::findOrFail($request->mustahik_id);
        $this->authorize('update', $mustahik);

        $saved = $this->service->upload(
            $mustahik,
            $request->file('files'),
            $request->jenis_berkas,
            $request->user(),
            $request->keterangan,
        );

        return response()->json([
            'message' => count($saved) . ' berkas berhasil diupload.',
            'berkas'  => collect($saved)->map(fn ($b) => [
                'id'           => $b->id,
                'nama_file'    => $b->nama_file,
                'jenis_berkas' => $b->jenis_berkas,
                'mime_type'    => $b->mime_type,
                'ukuran_format'=> $b->ukuran_format,
            ]),
        ]);
    }

    public function download(BerkasMustahik $berkas): StreamedResponse
    {
        $this->authorize('view', $berkas->mustahik);
        $this->authorize('berkas.download', $berkas->mustahik);

        return $this->service->download($berkas);
    }

    public function destroy(BerkasMustahik $berkas): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $berkas->mustahik);

        $this->service->delete($berkas);

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Berkas berhasil dihapus.']);
        }

        return back()->with('success', 'Berkas berhasil dihapus.');
    }
}
