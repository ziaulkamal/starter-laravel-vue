<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgramBantuan extends Model
{
    protected $table = 'program_bantuan';

    protected $fillable = [
        'nama_program',
        'tahun',
        'keterangan',
        'status',
        'target_penerima',
        'alokasi_dana',
        'tgl_mulai_pengajuan',
        'tgl_tutup_pengajuan',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tgl_mulai_pengajuan' => 'date',
            'tgl_tutup_pengajuan' => 'date',
            'alokasi_dana'        => 'decimal:2',
        ];
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pengajuanBantuan(): HasMany
    {
        return $this->hasMany(PengajuanBantuan::class, 'program_id');
    }

    public function penyaluranBantuan(): HasMany
    {
        return $this->hasMany(PenyaluranBantuan::class, 'program_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status', 'aktif');
    }

    public function scopeTahun(Builder $query, int $tahun): Builder
    {
        return $query->where('tahun', $tahun);
    }

    /** Cek apakah program sedang buka pengajuan */
    public function sedangBukaPengajuan(): bool
    {
        $now = now()->toDateString();
        return $this->status === 'aktif'
            && (!$this->tgl_mulai_pengajuan || $this->tgl_mulai_pengajuan->toDateString() <= $now)
            && (!$this->tgl_tutup_pengajuan || $this->tgl_tutup_pengajuan->toDateString() >= $now);
    }
}
