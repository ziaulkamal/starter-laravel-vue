<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodeZakat extends Model
{
    protected $table = 'periode_zakat';

    protected $fillable = [
        'kode_desa',
        'jenis_zakat',
        'bulan',
        'tahun',
        'status',
        'dikunci_oleh',
        'dikunci_pada',
    ];

    protected function casts(): array
    {
        return ['dikunci_pada' => 'datetime'];
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kode_desa', 'kode');
    }

    public function dikunciOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dikunci_oleh');
    }

    public function zakatRecords(): HasMany
    {
        return $this->hasMany(ZakatRecord::class, 'kode_desa', 'kode_desa')
            ->where('jenis_zakat', $this->jenis_zakat)
            ->where('bulan', $this->bulan)
            ->where('tahun', $this->tahun);
    }

    public function scopeFinal(Builder $query): Builder
    {
        return $query->where('status', 'final');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function isFinal(): bool
    {
        return $this->status === 'final';
    }
}
