<?php

namespace App\Notifications;

use App\Models\PengajuanEditPeserta;
use Illuminate\Notifications\Notification;

class PengajuanEditDibuatNotification extends Notification
{
    public function __construct(private PengajuanEditPeserta $pengajuan) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $peserta = $this->pengajuan->peserta;
        $requester = $this->pengajuan->user;

        return [
            'type'          => 'pengajuan_edit_dibuat',
            'pengajuan_id'  => $this->pengajuan->id,
            'peserta_id'    => $peserta->id,
            'peserta_nama'  => $peserta->nama,
            'peserta_status'=> $peserta->status,
            'requester_name'=> $requester->name,
            'kafilah_nama'  => $peserta->kafilah?->nama_kabupaten,
            'pesan'         => $this->pengajuan->pesan,
            'url'           => '/mtq/pengajuan-edit/manage',
        ];
    }
}
