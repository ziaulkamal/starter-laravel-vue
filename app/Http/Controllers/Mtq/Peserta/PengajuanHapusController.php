<?php

namespace App\Http\Controllers\Mtq\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Peserta;
use App\Models\PengajuanHapusPeserta;
use App\Models\User;
use App\Notifications\PengajuanHapusDibuatNotification;
use App\Notifications\PengajuanHapusDiresponNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PengajuanHapusController extends Controller
{
    /** Semua pengajuan hapus — hanya superadmin */
    public function manage(): Response
    {
        $pengajuans = PengajuanHapusPeserta::with(['peserta.kafilah', 'user', 'reviewer'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (PengajuanHapusPeserta $php) => [
                'id'             => $php->id,
                'peserta_id'     => $php->peserta_id,
                'peserta_nama'   => $php->peserta?->nama,
                'peserta_status' => $php->peserta?->status,
                'kafilah_nama'   => $php->peserta?->kafilah?->nama_kabupaten,
                'requester_name' => $php->user?->name,
                'pesan'          => $php->pesan,
                'status'         => $php->status,
                'catatan_admin'  => $php->catatan_admin,
                'reviewer_name'  => $php->reviewer?->name,
                'reviewed_at'    => $php->reviewed_at?->format('d M Y H:i'),
                'created_at'     => $php->created_at->format('d M Y H:i'),
            ]);

        return Inertia::render('Mtq/PengajuanHapus/Manage', [
            'pengajuans' => $pengajuans,
        ]);
    }

    /** Admin mengajukan permintaan hapus untuk peserta diverifikasi */
    public function store(Request $request, Peserta $peserta): RedirectResponse
    {
        $user = auth()->user();

        if ($peserta->status !== 'diverifikasi') {
            return back()->with('error', 'Pengajuan hapus hanya untuk peserta yang sudah diverifikasi.');
        }

        if ($peserta->pengajuanHapusMenunggu()->exists()) {
            return back()->with('error', 'Sudah ada pengajuan hapus yang sedang menunggu untuk peserta ini.');
        }

        $data = $request->validate([
            'pesan' => ['required', 'string', 'max:1000'],
        ]);

        $pengajuan = PengajuanHapusPeserta::create([
            'peserta_id' => $peserta->id,
            'user_id'    => $user->id,
            'pesan'      => $data['pesan'],
            'status'     => 'menunggu',
        ]);

        $pengajuan->load(['peserta.kafilah', 'user']);

        // Kirim notifikasi hanya ke superadmin
        $superadmins = User::role('superadmin')->get();
        Notification::send($superadmins, new PengajuanHapusDibuatNotification($pengajuan));

        return back()->with('success', 'Pengajuan hapus berhasil dikirim. Menunggu persetujuan superadmin.');
    }

    /** Superadmin menyetujui → peserta dihapus */
    public function approve(Request $request, PengajuanHapusPeserta $pengajuan): RedirectResponse
    {
        if ($pengajuan->status !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah direspon sebelumnya.');
        }

        $data = $request->validate([
            'catatan_admin' => ['nullable', 'string', 'max:500'],
        ]);

        $reviewer = auth()->user();

        $pengajuan->update([
            'status'        => 'disetujui',
            'catatan_admin' => $data['catatan_admin'] ?? null,
            'reviewed_by'   => $reviewer->id,
            'reviewed_at'   => now(),
        ]);

        // Hapus berkas lalu hapus peserta
        $peserta = $pengajuan->peserta;
        if ($peserta) {
            if ($peserta->foto) {
                Storage::disk('public')->delete($peserta->foto);
            }
            foreach ($peserta->berkas as $b) {
                Storage::disk('public')->delete($b->path);
            }
            $peserta->delete();
        }

        // Hapus notifikasi "pengajuan dibuat" dari topnav semua penerimanya
        DatabaseNotification::where('data->pengajuan_id', $pengajuan->id)
            ->where('type', PengajuanHapusDibuatNotification::class)
            ->delete();

        // Kirim notifikasi respons ke admin yang mengajukan
        $pengajuan->load(['peserta', 'reviewer']);
        $pengajuan->user?->notify(new PengajuanHapusDiresponNotification($pengajuan));

        return back()->with('success', 'Pengajuan disetujui. Peserta telah dihapus dari sistem.');
    }

    /** Superadmin menolak pengajuan hapus */
    public function reject(Request $request, PengajuanHapusPeserta $pengajuan): RedirectResponse
    {
        if ($pengajuan->status !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah direspon sebelumnya.');
        }

        $data = $request->validate([
            'catatan_admin' => ['required', 'string', 'max:500'],
        ]);

        $reviewer = auth()->user();

        $pengajuan->update([
            'status'        => 'ditolak',
            'catatan_admin' => $data['catatan_admin'],
            'reviewed_by'   => $reviewer->id,
            'reviewed_at'   => now(),
        ]);

        // Hapus notifikasi "pengajuan dibuat" dari topnav semua penerimanya
        DatabaseNotification::where('data->pengajuan_id', $pengajuan->id)
            ->where('type', PengajuanHapusDibuatNotification::class)
            ->delete();

        // Kirim notifikasi respons ke admin yang mengajukan
        $pengajuan->load(['peserta', 'reviewer']);
        $pengajuan->user?->notify(new PengajuanHapusDiresponNotification($pengajuan));

        return back()->with('success', 'Pengajuan hapus ditolak. Admin akan menerima notifikasi.');
    }
}
