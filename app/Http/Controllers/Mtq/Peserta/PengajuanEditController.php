<?php

namespace App\Http\Controllers\Mtq\Peserta;

use App\Http\Controllers\Controller;
use App\Models\Peserta;
use App\Models\PengajuanEditPeserta;
use App\Models\User;
use App\Notifications\PengajuanEditDibuatNotification;
use App\Notifications\PengajuanEditDiresponNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class PengajuanEditController extends Controller
{
    /** Daftar pengajuan milik user sendiri (role: user) */
    public function index(): Response
    {
        $user = auth()->user();

        $pengajuans = PengajuanEditPeserta::with(['peserta.kafilah', 'reviewer'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (PengajuanEditPeserta $pep) => [
                'id'             => $pep->id,
                'peserta_id'     => $pep->peserta_id,
                'peserta_nama'   => $pep->peserta?->nama,
                'peserta_status' => $pep->peserta?->status,
                'kafilah_nama'   => $pep->peserta?->kafilah?->nama_kabupaten,
                'pesan'          => $pep->pesan,
                'status'         => $pep->status,
                'catatan_admin'  => $pep->catatan_admin,
                'reviewer_name'  => $pep->reviewer?->name,
                'reviewed_at'    => $pep->reviewed_at?->format('d M Y H:i'),
                'created_at'     => $pep->created_at->format('d M Y H:i'),
            ]);

        return Inertia::render('Mtq/PengajuanEdit/Index', [
            'pengajuans' => $pengajuans,
        ]);
    }

    /** Daftar semua pengajuan masuk (role: admin, superadmin) */
    public function manage(): Response
    {
        $pengajuans = PengajuanEditPeserta::with(['peserta.kafilah', 'user', 'reviewer'])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (PengajuanEditPeserta $pep) => [
                'id'             => $pep->id,
                'peserta_id'     => $pep->peserta_id,
                'peserta_nama'   => $pep->peserta?->nama,
                'peserta_status' => $pep->peserta?->status,
                'kafilah_nama'   => $pep->peserta?->kafilah?->nama_kabupaten,
                'requester_name' => $pep->user?->name,
                'pesan'          => $pep->pesan,
                'status'         => $pep->status,
                'catatan_admin'  => $pep->catatan_admin,
                'reviewer_name'  => $pep->reviewer?->name,
                'reviewed_at'    => $pep->reviewed_at?->format('d M Y H:i'),
                'created_at'     => $pep->created_at->format('d M Y H:i'),
            ]);

        return Inertia::render('Mtq/PengajuanEdit/Manage', [
            'pengajuans' => $pengajuans,
            'userRole'   => auth()->user()->getRoleNames()->first(),
        ]);
    }

    /** User membuat pengajuan edit */
    public function store(Request $request, Peserta $peserta): RedirectResponse
    {
        $user = auth()->user();

        // Ownership guard untuk user role
        if ($user->hasRole('user') && $peserta->kafilah_id !== $user->kafilah_id) {
            abort(403);
        }

        if (!in_array($peserta->status, ['ditolak', 'diverifikasi'])) {
            return back()->with('error', 'Pengajuan edit hanya bisa dilakukan untuk peserta dengan status ditolak atau diverifikasi.');
        }

        // Cek apakah sudah ada pengajuan menunggu
        if ($peserta->pengajuanEditMenunggu()->exists()) {
            return back()->with('error', 'Sudah ada pengajuan edit yang sedang menunggu untuk peserta ini.');
        }

        $data = $request->validate([
            'pesan' => ['required', 'string', 'max:1000'],
        ]);

        $pengajuan = PengajuanEditPeserta::create([
            'peserta_id' => $peserta->id,
            'user_id'    => $user->id,
            'pesan'      => $data['pesan'],
            'status'     => 'menunggu',
        ]);

        $pengajuan->load(['peserta.kafilah', 'user']);

        // Kirim notifikasi ke semua admin dan superadmin
        $recipients = User::role(['admin', 'superadmin'])->get();
        Notification::send($recipients, new PengajuanEditDibuatNotification($pengajuan));

        return back()->with('success', 'Pengajuan edit berhasil dikirim. Menunggu persetujuan admin.');
    }

    /** Admin/Superadmin menyetujui pengajuan */
    public function approve(Request $request, PengajuanEditPeserta $pengajuan): RedirectResponse
    {
        $reviewer = auth()->user();

        if ($pengajuan->status !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah direspon sebelumnya.');
        }

        // Jika peserta berstatus diverifikasi, hanya superadmin yang bisa approve
        $peserta = $pengajuan->peserta;
        if ($peserta->status === 'diverifikasi' && !$reviewer->hasRole('superadmin')) {
            return back()->with('error', 'Hanya superadmin yang dapat menyetujui pengajuan edit peserta yang sudah diverifikasi.');
        }

        $data = $request->validate([
            'catatan_admin' => ['nullable', 'string', 'max:500'],
        ]);

        $pengajuan->update([
            'status'        => 'disetujui',
            'catatan_admin' => $data['catatan_admin'] ?? null,
            'reviewed_by'   => $reviewer->id,
            'reviewed_at'   => now(),
        ]);

        // Kembalikan peserta ke status draft
        $peserta->update(['status' => 'draft']);

        // Hapus notifikasi "pengajuan dibuat" dari topnav semua penerimanya
        DatabaseNotification::where('data->pengajuan_id', $pengajuan->id)
            ->where('type', PengajuanEditDibuatNotification::class)
            ->delete();

        // Kirim notifikasi respons ke user yang mengajukan
        $pengajuan->load(['peserta', 'reviewer']);
        $pengajuan->user->notify(new PengajuanEditDiresponNotification($pengajuan));

        return back()->with('success', 'Pengajuan disetujui. Peserta dikembalikan ke status draft.');
    }

    /** Admin/Superadmin menolak pengajuan */
    public function reject(Request $request, PengajuanEditPeserta $pengajuan): RedirectResponse
    {
        $reviewer = auth()->user();

        if ($pengajuan->status !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah direspon sebelumnya.');
        }

        // Jika peserta berstatus diverifikasi, hanya superadmin yang bisa reject
        $peserta = $pengajuan->peserta;
        if ($peserta->status === 'diverifikasi' && !$reviewer->hasRole('superadmin')) {
            return back()->with('error', 'Hanya superadmin yang dapat merespon pengajuan edit peserta yang sudah diverifikasi.');
        }

        $data = $request->validate([
            'catatan_admin' => ['required', 'string', 'max:500'],
        ]);

        $pengajuan->update([
            'status'        => 'ditolak',
            'catatan_admin' => $data['catatan_admin'],
            'reviewed_by'   => $reviewer->id,
            'reviewed_at'   => now(),
        ]);

        // Hapus notifikasi "pengajuan dibuat" dari topnav semua penerimanya
        DatabaseNotification::where('data->pengajuan_id', $pengajuan->id)
            ->where('type', PengajuanEditDibuatNotification::class)
            ->delete();

        // Kirim notifikasi respons ke user yang mengajukan
        $pengajuan->load(['peserta', 'reviewer']);
        $pengajuan->user->notify(new PengajuanEditDiresponNotification($pengajuan));

        return back()->with('success', 'Pengajuan ditolak. User akan menerima notifikasi.');
    }
}
