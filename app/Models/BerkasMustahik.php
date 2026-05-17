<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BerkasMustahik extends Model
{
    protected $table = 'berkas_mustahik';

    protected $fillable = [
        'mustahik_id',
        'uploaded_by',
        'jenis_berkas',
        'nama_file',
        'path_file',
        'ukuran_file',
        'mime_type',
        'keterangan',
    ];

    public function mustahik(): BelongsTo
    {
        return $this->belongsTo(Mustahik::class, 'mustahik_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function getUkuranFormatAttribute(): string
    {
        $kb = $this->ukuran_file / 1024;
        return $kb >= 1024
            ? number_format($kb / 1024, 1) . ' MB'
            : number_format($kb, 0) . ' KB';
    }
}
