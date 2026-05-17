<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mustahik extends Model
{
    use SoftDeletes;

    protected $table = 'mustahik';

    protected $fillable = [
        'kode_provinsi',
        'kode_kabupaten',
        'kode_kecamatan',
        'kode_desa',
        'created_by',
        'nama_lengkap',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'nik',
        'alamat_gampong',
        'alamat_dusun',
        'no_hp',
        'pemilik_rekening',
        'nomor_rekening',
        'pekerjaan',
        'status_pernikahan',
        'jumlah_anggota',
        'jumlah_tanggungan',
        'range_penghasilan',
        'status_pencari_nafkah',
        'status',
        'catatan',
        'survey_filled_at',
        'survey_filled_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir'   => 'date',
            'survey_filled_at' => 'datetime',
        ];
    }

    // ── Relasi Wilayah ───────────────────────────────────────────────

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kode_provinsi', 'kode');
    }

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kode_kabupaten', 'kode');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kode_kecamatan', 'kode');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kode_desa', 'kode');
    }

    // ── Relasi Lain ──────────────────────────────────────────────────

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function surveyFilledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'survey_filled_by');
    }

    public function berkasFiles(): HasMany
    {
        return $this->hasMany(BerkasMustahik::class, 'mustahik_id');
    }

    public function pengajuanBantuan(): HasMany
    {
        return $this->hasMany(PengajuanBantuan::class, 'mustahik_id');
    }

    public function penyaluranBantuan(): HasMany
    {
        return $this->hasMany(PenyaluranBantuan::class, 'mustahik_id');
    }

    // ── Scopes ───────────────────────────────────────────────────────

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function scopeByDesa(Builder $query, string $kodeDesa): Builder
    {
        return $query->where('kode_desa', $kodeDesa);
    }

    public function scopeByKecamatan(Builder $query, string $kodeKecamatan): Builder
    {
        return $query->where('kode_kecamatan', $kodeKecamatan);
    }
}
