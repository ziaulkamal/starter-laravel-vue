<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProfileMenuItem extends Model
{
    protected $fillable = ['label', 'icon', 'href', 'order_index', 'is_active'];

    protected $casts = [
        'is_active'   => 'boolean',
        'order_index' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
