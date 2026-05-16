<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'deskripsi', 'is_active'])]
class Cabang extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function golongans(): HasMany
    {
        return $this->hasMany(Golongan::class);
    }

    public function kriterias(): HasMany
    {
        return $this->hasMany(Kriteria::class);
    }
}
