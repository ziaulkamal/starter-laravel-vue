<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Wilayah extends Model
{
    protected $table      = 'wilayah';
    protected $primaryKey = 'kode';
    protected $keyType    = 'string';
    public $incrementing  = false;
    public $timestamps    = false;

    protected $fillable = ['kode', 'nama'];

    // ── Level helpers ────────────────────────────────────────────────

    /** Provinsi: kode 2 chars (e.g. "11") */
    public function scopeProvinsi(Builder $query): Builder
    {
        return $query->whereRaw('LENGTH(kode) = 2');
    }

    /** Kabupaten/Kota: kode 5 chars (e.g. "11.12") */
    public function scopeKabupaten(Builder $query, ?string $kodeProvinsi = null): Builder
    {
        $query->whereRaw('LENGTH(kode) = 5');
        if ($kodeProvinsi) {
            $query->where('kode', 'like', $kodeProvinsi . '.%');
        }
        return $query;
    }

    /** Kecamatan: kode 8 chars (e.g. "11.12.01") */
    public function scopeKecamatan(Builder $query, ?string $kodeKabupaten = null): Builder
    {
        $query->whereRaw('LENGTH(kode) = 8');
        if ($kodeKabupaten) {
            $query->where('kode', 'like', $kodeKabupaten . '.%');
        }
        return $query;
    }

    /** Desa/Gampong: kode 13 chars (e.g. "11.12.01.2001") */
    public function scopeDesa(Builder $query, ?string $kodeKecamatan = null): Builder
    {
        $query->whereRaw('LENGTH(kode) = 13');
        if ($kodeKecamatan) {
            $query->where('kode', 'like', $kodeKecamatan . '.%');
        }
        return $query;
    }

    /** Filter hanya wilayah Aceh Barat Daya (11.12) */
    public function scopeAbdya(Builder $query): Builder
    {
        return $query->where('kode', 'like', '11.12%');
    }

    // ── Accessors ────────────────────────────────────────────────────

    public function getLevelAttribute(): string
    {
        return match (strlen($this->kode)) {
            2       => 'provinsi',
            5       => 'kabupaten',
            8       => 'kecamatan',
            13      => 'desa',
            default => 'unknown',
        };
    }

    public function getKodeProvinsiAttribute(): string
    {
        return substr($this->kode, 0, 2);
    }

    public function getKodeKabupatenAttribute(): ?string
    {
        return strlen($this->kode) >= 5 ? substr($this->kode, 0, 5) : null;
    }

    public function getKodeKecamatanAttribute(): ?string
    {
        return strlen($this->kode) >= 8 ? substr($this->kode, 0, 8) : null;
    }
}
