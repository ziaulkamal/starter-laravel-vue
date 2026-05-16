<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['tahun', 'tema', 'tgl_mulai_daftar', 'tgl_tutup_daftar', 'tgl_pelaksanaan'])]
class KonfigurasiEvent extends Model
{
    protected function casts(): array
    {
        return [
            'tahun'           => 'integer',
            'tgl_mulai_daftar' => 'date',
            'tgl_tutup_daftar' => 'date',
            'tgl_pelaksanaan'  => 'date',
        ];
    }
}
