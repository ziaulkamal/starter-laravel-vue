<?php

namespace App\Notifications;

use App\Models\PengajuanEditPeserta;
use Illuminate\Notifications\Notification;

class PengajuanEditDiresponNotification extends Notification
{
    public function __construct(private PengajuanEditPeserta $pengajuan) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $peserta = $this->pengajuan->peserta;
        $disetujui = $this->pengajuan->status === 'disetujui';

        return [
            'type'           => 'pengajuan_edit_direspon',
            'pengajuan_id'   => $this->pengajuan->id,
            'peserta_id'     => $peserta->id,
            'peserta_nama'   => $peserta->nama,
            'status'         => $this->pengajuan->status,
            'catatan_admin'  => $this->pengajuan->catatan_admin,
            'reviewer_name'  => $this->pengajuan->reviewer?->name,
            'message'        => $disetujui
                ? "Pengajuan edit untuk peserta {$peserta->nama} telah disetujui. Silakan edit dan ajukan kembali."
                : "Pengajuan edit untuk peserta {$peserta->nama} ditolak.",
            'url'            => '/mtq/pengajuan-edit',
        ];
    }
}
