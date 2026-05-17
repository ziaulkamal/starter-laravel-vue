<?php

namespace App\Services;

use App\Models\BerkasMustahik;
use App\Models\Mustahik;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BerkasService
{
    private string $disk = 'local';
    private string $basePath = 'berkas-mustahik';

    public function upload(Mustahik $mustahik, array $files, string $jenisBerkas, User $uploader, ?string $keterangan = null): array
    {
        $saved = [];

        foreach ($files as $file) {
            /** @var UploadedFile $file */
            $dir      = "{$this->basePath}/{$mustahik->id}";
            $filename = $jenisBerkas . '_' . now()->format('YmdHis') . '_' . $file->getClientOriginalName();
            $path     = $file->storeAs($dir, $filename, $this->disk);

            $saved[] = BerkasMustahik::create([
                'mustahik_id'  => $mustahik->id,
                'uploaded_by'  => $uploader->id,
                'jenis_berkas' => $jenisBerkas,
                'nama_file'    => $file->getClientOriginalName(),
                'path_file'    => $path,
                'ukuran_file'  => $file->getSize(),
                'mime_type'    => $file->getMimeType(),
                'keterangan'   => $keterangan,
            ]);
        }

        return $saved;
    }

    public function download(BerkasMustahik $berkas): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless(Storage::disk($this->disk)->exists($berkas->path_file), 404);

        return Storage::disk($this->disk)->download($berkas->path_file, $berkas->nama_file);
    }

    public function delete(BerkasMustahik $berkas): void
    {
        Storage::disk($this->disk)->delete($berkas->path_file);
        $berkas->delete();
    }
}
