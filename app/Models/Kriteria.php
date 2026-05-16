<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['cabang_id', 'nama', 'bobot', 'min_nilai', 'max_nilai'])]
class Kriteria extends Model
{
    protected function casts(): array
    {
        return [
            'bobot'     => 'decimal:2',
            'min_nilai' => 'decimal:2',
            'max_nilai' => 'decimal:2',
        ];
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }
}
