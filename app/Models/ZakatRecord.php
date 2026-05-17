<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ZakatRecord extends Model
{
    protected $table = 'zakat_records';

    protected $fillable = [
        'kode_desa',
        'muzakki_id',
        'dicatat_oleh',
        'jenis_zakat',
        'nama_muzakki_manual',
        'kode_kecamatan_muzakki',
        'kode_desa_muzakki',
        'jumlah_jiwa',
        'jumlah_beras_kg',
        'jumlah_uang',
        'jenis_harta',
        'tanggal_penerimaan',
        'bulan',
        'tahun',
        'tahun_hijriah',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penerimaan' => 'date',
            'jumlah_beras_kg'    => 'decimal:2',
            'jumlah_uang'        => 'decimal:2',
        ];
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kode_desa', 'kode');
    }

    public function muzakki(): BelongsTo
    {
        return $this->belongsTo(Muzakki::class, 'muzakki_id');
    }

    public function dicatatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function kecamatanMuzakki(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kode_kecamatan_muzakki', 'kode');
    }

    public function desaMuzakki(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kode_desa_muzakki', 'kode');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeFitrah(Builder $query): Builder
    {
        return $query->where('jenis_zakat', 'fitrah');
    }

    public function scopeMal(Builder $query): Builder
    {
        return $query->where('jenis_zakat', 'mal');
    }

    public function scopeByPeriode(Builder $query, int $bulan, int $tahun): Builder
    {
        return $query->where('bulan', $bulan)->where('tahun', $tahun);
    }

    public function scopeByDesa(Builder $query, string $kodeDesa): Builder
    {
        return $query->where('kode_desa', $kodeDesa);
    }

    /** Nama muzakki: dari relasi atau manual */
    public function getNamaMuzakkiAttribute(): string
    {
        return $this->muzakki?->nama_lengkap ?? $this->nama_muzakki_manual ?? '-';
    }
}
