<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
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

    public function pengajuanEdit(): HasMany
    {
        return $this->hasMany(PengajuanEditPeserta::class);
    }

    public function pengajuanEditMenunggu(): HasMany
    {
        return $this->hasMany(PengajuanEditPeserta::class)->where('status', 'menunggu');
    }

    public function pengajuanHapus(): HasMany
    {
        return $this->hasMany(PengajuanHapusPeserta::class);
    }

    public function pengajuanHapusMenunggu(): HasMany
    {
        return $this->hasMany(PengajuanHapusPeserta::class)->where('status', 'menunggu');
    }

    public function scopeForUser(Builder $query, ?int $kafilahId): Builder
    {
        if ($kafilahId === null) {
            return $query->whereRaw('0 = 1');
        }
        return $query->where('kafilah_id', $kafilahId);
    }
}
