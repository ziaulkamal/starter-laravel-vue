<?php

namespace App\Notifications;

use App\Models\PengajuanHapusPeserta;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PengajuanHapusDiresponNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly PengajuanHapusPeserta $pengajuan) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $peserta  = $this->pengajuan->peserta;
        $reviewer = $this->pengajuan->reviewer;
        $disetujui = $this->pengajuan->status === 'disetujui';

        return [
            'type'           => 'pengajuan_hapus_direspon',
            'pengajuan_id'   => $this->pengajuan->id,
            'peserta_id'     => $peserta?->id,
            'peserta_nama'   => $peserta?->nama,
            'status'         => $this->pengajuan->status,
            'catatan_admin'  => $this->pengajuan->catatan_admin,
            'reviewer_name'  => $reviewer?->name,
            'message'        => $disetujui
                ? "Pengajuan hapus peserta {$peserta?->nama} telah disetujui. Peserta telah dihapus dari sistem."
                : "Pengajuan hapus peserta {$peserta?->nama} ditolak.",
            'url'            => '/mtq/peserta',
        ];
    }
}
