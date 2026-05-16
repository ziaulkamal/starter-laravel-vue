<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['peserta_id', 'jenis', 'path', 'nama_asli', 'mime_type', 'ukuran'])]
class BerkasPeserta extends Model
{
    protected function casts(): array
    {
        return [
            'ukuran' => 'integer',
        ];
    }

    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class);
    }
}
