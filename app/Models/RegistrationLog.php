<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationLog extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'ip_address',
        'registered_at',
        'action',
        'notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
        'processed_at'  => 'datetime',
    ];

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
