<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'kafilah_id', 'cabang_id', 'golongan_id',
    'nama', 'nik', 'tempat_lahir', 'tgl_lahir', 'jenis_kelamin', 'alamat', 'kode_wilayah_desa', 'foto',
    'status', 'nomor_peserta', 'catatan_verifikasi',
])]
class Peserta extends Model
{
    protected function casts(): array
    {
        return [
            'tgl_lahir' => 'date',
        ];
    }

    public function kafilah(): BelongsTo
    {
        return $this->belongsTo(Kafilah::class);
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }

    public function golongan(): BelongsTo
    {
        return $this->belongsTo(Golongan::class);
    }

    public function berkas(): HasMany
    {
        return $this->hasMany(BerkasPeserta::class);
    }
}
