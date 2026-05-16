<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama_kabupaten', 'kode_wilayah', 'koordinator_nama', 'koordinator_kontak'])]
class Kafilah extends Model
{
    protected function casts(): array
    {
        return [];
    }
}
