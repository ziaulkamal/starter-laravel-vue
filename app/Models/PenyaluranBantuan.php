<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenyaluranBantuan extends Model
{
    protected $table = 'penyaluran_bantuan';

    protected $fillable = [
        'pengajuan_id',
        'mustahik_id',
        'program_id',
        'disalurkan_oleh',
        'jenis_bantuan',
        'jumlah_bantuan',
        'satuan',
        'tanggal_penyaluran',
        'keterangan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_penyaluran' => 'date',
            'jumlah_bantuan'     => 'decimal:2',
        ];
    }

    public function pengajuan(): BelongsTo
    {
        return $this->belongsTo(PengajuanBantuan::class, 'pengajuan_id');
    }

    public function mustahik(): BelongsTo
    {
        return $this->belongsTo(Mustahik::class, 'mustahik_id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(ProgramBantuan::class, 'program_id');
    }

    public function disalurkanOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disalurkan_oleh');
    }
}
