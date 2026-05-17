<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengajuanBantuan extends Model
{
    protected $table = 'pengajuan_bantuan';

    protected $fillable = [
        'mustahik_id',
        'substansi_kategori_id',
        'kode_desa',
        'tahun',
        'diajukan_oleh',
        'diputuskan_oleh',
        'status',
        'catatan_pengaju',
        'alasan_penolakan',
        'tanggal_pengajuan',
        'tanggal_keputusan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pengajuan' => 'date',
            'tanggal_keputusan' => 'date',
            'tahun'             => 'integer',
        ];
    }

    public function mustahik(): BelongsTo
    {
        return $this->belongsTo(Mustahik::class, 'mustahik_id');
    }

    public function substansiKategori(): BelongsTo
    {
        return $this->belongsTo(SubstansiKategori::class, 'substansi_kategori_id');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kode_desa', 'kode');
    }

    public function diajukanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diajukan_oleh');
    }

    public function diputuskanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diputuskan_oleh');
    }

    // ── Scopes ─────────────────────────────────────────────────────────

    public function scopeMenunggu(Builder $query): Builder
    {
        return $query->whereIn('status', ['diajukan', 'sanggah']);
    }

    public function scopeDisetujui(Builder $query): Builder
    {
        return $query->where('status', 'disetujui');
    }

    public function scopeByDesa(Builder $query, string $kodeDesa): Builder
    {
        return $query->where('kode_desa', $kodeDesa);
    }

    public function scopeByTahun(Builder $query, int $tahun): Builder
    {
        return $query->where('tahun', $tahun);
    }
}
