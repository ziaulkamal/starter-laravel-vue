<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubstansiKategori extends Model
{
    protected $table = 'substansi_kategori';

    protected $fillable = ['nama', 'kode_asnaf', 'keterangan', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function pengajuanBantuan(): HasMany
    {
        return $this->hasMany(PengajuanBantuan::class, 'substansi_kategori_id');
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
