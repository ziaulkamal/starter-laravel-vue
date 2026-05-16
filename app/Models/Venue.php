<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama', 'alamat', 'lat', 'lng', 'kapasitas'])]
class Venue extends Model
{
    protected function casts(): array
    {
        return [
            'lat'      => 'decimal:8',
            'lng'      => 'decimal:8',
            'kapasitas' => 'integer',
        ];
    }
}
