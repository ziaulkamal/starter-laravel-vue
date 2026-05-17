<?php

namespace App\Http\Controllers\Mtq\Peserta;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mtq\Peserta\StorePesertaRequest;
use App\Http\Requests\Mtq\Peserta\UpdatePesertaRequest;
use App\Models\BerkasPeserta;
use App\Models\Cabang;
use App\Models\Golongan;
use App\Models\Kafilah;
use App\Models\Kriteria;
use App\Models\Peserta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PesertaController extends Controller
{
    public function index(): Response
    {
        $user  = auth()->user();
        $query = Peserta::with(['kafilah', 'cabang', 'golongan', 'berkas',
                                'pengajuanEditMenunggu', 'pengajuanHapusMenunggu']);

        if ($user->hasRole('user')) {
            $query->forUser($user->kafilah_id);
        }

        $peserta = $query->orderByDesc('created_at')
            ->get()
            ->map(fn (Peserta $p) => [
                'id'                    => $p->id,
                'kafilah_id'            => $p->kafilah_id,
                'kafilah_nama'          => $p->kafilah?->nama_kabupaten,
                'cabang_id'             => $p->cabang_id,
                'cabang_nama'           => $p->cabang?->nama,
                'golongan_id'           => $p->golongan_id,
                'golongan_nama'         => $p->golongan?->nama,
                'nama'                  => $p->nama,
                'nik'                   => $p->nik,
                'jenis_kelamin'         => $p->jenis_kelamin,
                'tempat_lahir'          => $p->tempat_lahir,
                'tgl_lahir'             => $p->tgl_lahir?->format('Y-m-d'),
                'alamat'                => $p->alamat,
                'foto_url'              => $p->foto ? Storage::url($p->foto) : null,
                'kode_wilayah_desa'     => $p->kode_wilayah_desa,
                'status'                => $p->status,
                'catatan_verifikasi'    => $p->catatan_verifikasi,
                'nomor_peserta'         => $p->nomor_peserta,
                'has_pending_pengajuan' => $p->pengajuanEditMenunggu->isNotEmpty(),
                'has_pending_hapus'     => $p->pengajuanHapusMenunggu->isNotEmpty(),
                'berkas'                => $p->berkas->map(fn (BerkasPeserta $b) => [
                    'id'        => $b->id,
                    'jenis'     => $b->jenis,
                    'url'       => Storage::url($b->path),
                    'nama_asli' => $b->nama_asli,
                    'mime_type' => $b->mime_type,
                    'ukuran'    => $b->ukuran,
                ])->all(),
            ]);

        return Inertia::render('Mtq/Peserta/Index', [
            'peserta'       => $peserta,
            'kafilahs'      => Kafilah::orderBy('nama_kabupaten')->get(['id', 'nama_kabupaten']),
            'cabangs'       => Cabang::where('is_active', true)->orderBy('nama')->get(['id', 'nama']),
            'golongans'     => Golongan::orderBy('nama')->get(['id', 'cabang_id', 'nama', 'jenis_kelamin']),
            'kriteria'      => Kriteria::orderBy('nama')->get(['id', 'cabang_id', 'nama']),
            'userKafilahId' => $user->kafilah_id,
            'userRole'      => $user->getRoleNames()->first(),
        ]);
    }

    public function store(StorePesertaRequest $request): RedirectResponse
    {
        $user = auth()->user();
        $data = $request->validated();

        if ($user->hasRole('user')) {
            $data['kafilah_id'] = $user->kafilah_id;
        }

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('peserta/foto', 'public');
        }

        Peserta::create($data);
        return back()->with('success', 'Peserta berhasil didaftarkan.');
    }

    public function update(UpdatePesertaRequest $request, Peserta $peserta): RedirectResponse
    {
        $this->authorizeOwnership($peserta);

        $data = $request->validated();

        if ($request->hasFile('foto')) {
            if ($peserta->foto) {
                Storage::disk('public')->delete($peserta->foto);
            }
            $data['foto'] = $request->file('foto')->store('peserta/foto', 'public');
        } else {
            unset($data['foto']);
        }

        $peserta->update($data);
        return back()->with('success', 'Data peserta berhasil diperbarui.');
    }

    public function destroy(Peserta $peserta): RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasRole('user')) {
            abort(403, 'Hanya admin dan superadmin yang dapat menghapus peserta.');
        }

        // Admin tidak boleh langsung hapus peserta yang sudah diverifikasi
        if ($peserta->status === 'diverifikasi' && !$user->hasRole('superadmin')) {
            return back()->with('error', 'Peserta yang sudah diverifikasi hanya dapat dihapus oleh superadmin. Gunakan fitur Ajukan Hapus.');
        }

        if ($peserta->foto) {
            Storage::disk('public')->delete($peserta->foto);
        }
        foreach ($peserta->berkas as $b) {
            Storage::disk('public')->delete($b->path);
        }
        $peserta->delete();
        return back()->with('success', 'Peserta berhasil dihapus.');
    }

    public function submit(Peserta $peserta): RedirectResponse
    {
        $this->authorizeOwnership($peserta);

        if ($peserta->status !== 'draft') {
            return back()->with('error', 'Peserta tidak dalam status draft.');
        }
        $peserta->update(['status' => 'diajukan']);
        return back()->with('success', 'Peserta berhasil diajukan untuk verifikasi.');
    }

    public function verify(Request $request, Peserta $peserta): RedirectResponse
    {
        if ($peserta->status !== 'diajukan') {
            return back()->with('error', 'Peserta belum diajukan untuk verifikasi.');
        }
        $data = $request->validate([
            'nomor_peserta' => ['required', 'string', 'max:20', 'unique:pesertas,nomor_peserta,' . $peserta->id],
        ]);
        $peserta->update([
            'status'             => 'diverifikasi',
            'nomor_peserta'      => $data['nomor_peserta'],
            'catatan_verifikasi' => null,
        ]);
        return back()->with('success', 'Peserta berhasil diverifikasi. Nomor: ' . $data['nomor_peserta']);
    }

    public function reject(Request $request, Peserta $peserta): RedirectResponse
    {
        if ($peserta->status !== 'diajukan') {
            return back()->with('error', 'Peserta belum diajukan untuk verifikasi.');
        }
        $data = $request->validate([
            'catatan_verifikasi' => ['required', 'string', 'max:500'],
        ]);
        $peserta->update([
            'status'             => 'ditolak',
            'catatan_verifikasi' => $data['catatan_verifikasi'],
        ]);
        return back()->with('success', 'Peserta telah ditolak.');
    }

    public function updateFoto(Request $request, Peserta $peserta): RedirectResponse
    {
        $this->authorizeOwnership($peserta);

        $request->validate([
            'foto' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:2048',
                       'dimensions:min_width=300,min_height=400'],
        ], [
            'foto.required'   => 'Pilih file foto terlebih dahulu.',
            'foto.image'      => 'File harus berupa gambar.',
            'foto.mimes'      => 'Format foto harus JPG atau PNG.',
            'foto.max'        => 'Ukuran foto maksimal 2 MB.',
            'foto.dimensions' => 'Resolusi foto minimal 300×400 piksel (rasio 3×4).',
        ]);

        if ($peserta->foto) {
            Storage::disk('public')->delete($peserta->foto);
        }

        $peserta->update([
            'foto' => $request->file('foto')->store('peserta/foto', 'public'),
        ]);

        return back()->with('success', 'Pas foto berhasil diperbarui.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasRole('user')) {
            abort(403, 'Hanya admin dan superadmin yang dapat menghapus peserta.');
        }

        $ids   = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];
        $query = Peserta::with('berkas')->whereIn('id', $ids);

        // Admin tidak boleh bulk-hapus peserta yang sudah diverifikasi
        if (!$user->hasRole('superadmin')) {
            $query->where('status', '!=', 'diverifikasi');
        }

        $pesertas = $query->get();
        foreach ($pesertas as $p) {
            if ($p->foto) {
                Storage::disk('public')->delete($p->foto);
            }
            foreach ($p->berkas as $b) {
                Storage::disk('public')->delete($b->path);
            }
        }
        Peserta::whereIn('id', $pesertas->pluck('id'))->delete();
        return back()->with('success', $pesertas->count() . ' peserta berhasil dihapus.');
    }

    private function authorizeOwnership(Peserta $peserta): void
    {
        $user = auth()->user();
        if ($user->hasRole('user') && $peserta->kafilah_id !== $user->kafilah_id) {
            abort(403);
        }
    }
}
