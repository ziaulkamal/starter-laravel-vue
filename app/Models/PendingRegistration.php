<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingRegistration extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'password', 'ip_address', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
