<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Muzakki extends Model
{
    protected $fillable = [
        'kode_desa',
        'created_by',
        'nama_lengkap',
        'nik',
        'no_hp',
    ];

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Wilayah::class, 'kode_desa', 'kode');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function zakatRecords(): HasMany
    {
        return $this->hasMany(ZakatRecord::class, 'muzakki_id');
    }

    public function scopeByDesa(Builder $query, string $kodeDesa): Builder
    {
        return $query->where('kode_desa', $kodeDesa);
    }
}
