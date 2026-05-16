<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['cabang_id', 'nama', 'min_usia', 'max_usia', 'jenis_kelamin'])]
class Golongan extends Model
{
    protected function casts(): array
    {
        return [
            'min_usia' => 'integer',
            'max_usia' => 'integer',
        ];
    }

    public function cabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class);
    }
}
