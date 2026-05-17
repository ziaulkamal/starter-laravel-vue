<?php

namespace App\Notifications;

use App\Models\PengajuanHapusPeserta;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PengajuanHapusDibuatNotification extends Notification
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
        $requester = $this->pengajuan->user;

        return [
            'type'           => 'pengajuan_hapus_dibuat',
            'pengajuan_id'   => $this->pengajuan->id,
            'peserta_id'     => $peserta?->id,
            'peserta_nama'   => $peserta?->nama,
            'peserta_status' => $peserta?->status,
            'kafilah_nama'   => $peserta?->kafilah?->nama_kabupaten,
            'requester_name' => $requester?->name,
            'pesan'          => $this->pengajuan->pesan,
            'url'            => '/mtq/pengajuan-hapus/manage',
        ];
    }
}
