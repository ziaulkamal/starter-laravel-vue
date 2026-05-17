<?php

namespace App\Http\Controllers\Gampong;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGampongMustahikRequest;
use App\Http\Requests\UpdateGampongMustahikRequest;
use App\Models\Mustahik;
use App\Models\PengajuanBantuan;
use App\Models\SubstansiKategori;
use App\Models\Wilayah;
use App\Repositories\MustahikRepository;
use App\Services\MustahikService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MustahikController extends Controller
{
    public function __construct(
        private MustahikRepository $repository,
        private MustahikService    $service,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Mustahik::class);

        $kodeDesa = $request->user()->kode_wilayah;
        $filters  = $request->only(['search', 'status', 'jenis_kelamin', 'range_penghasilan']);

        return Inertia::render('Gampong/Mustahik/Index', [
            'mustahik'    => $this->repository->paginate($filters, kodeDesa: $kodeDesa, perPage: 20),
            'filters'     => $filters,
            'nama_wilayah'=> Wilayah::find($kodeDesa)?->nama,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Mustahik::class);

        $user     = request()->user();
        $kodeDesa = $user->kode_wilayah;

        // Derive parent codes dari kode_desa
        $desa = Wilayah::find($kodeDesa);

        return Inertia::render('Gampong/Mustahik/Create', [
            'wilayah' => [
                'kode_desa'       => $kodeDesa,
                'kode_kecamatan'  => $desa ? substr($kodeDesa, 0, 8) : null,
                'kode_kabupaten'  => $desa ? substr($kodeDesa, 0, 5) : null,
                'kode_provinsi'   => $desa ? substr($kodeDesa, 0, 2) : null,
                'nama_desa'       => $desa?->nama,
                'nama_kecamatan'  => Wilayah::find(substr($kodeDesa, 0, 8))?->nama,
                'nama_kabupaten'  => Wilayah::find(substr($kodeDesa, 0, 5))?->nama,
                'nama_provinsi'   => Wilayah::find(substr($kodeDesa, 0, 2))?->nama,
            ],
        ]);
    }

    public function store(StoreGampongMustahikRequest $request): RedirectResponse
    {
        // Paksa kode_desa sesuai wilayah admin gampong
        $data             = $request->validated();
        $data['kode_desa']= $request->user()->kode_wilayah;

        $mustahik = $this->service->create($data, $request->user());

        return redirect()
            ->route('gampong.mustahik.show', $mustahik)
            ->with('success', 'Data mustahik berhasil ditambahkan.');
    }

    public function show(Mustahik $mustahik): Response
    {
        $this->authorize('view', $mustahik);

        $tahun           = now()->year;
        $pengajuanTahunIni = PengajuanBantuan::where('mustahik_id', $mustahik->id)
            ->where('tahun', $tahun)
            ->with(['substansiKategori:id,nama,kode_asnaf', 'diputuskanOleh:id,name'])
            ->first();

        $canAjukan = $mustahik->status === 'aktif'
            && is_null($pengajuanTahunIni);

        return Inertia::render('Mustahik/Show', [
            'mustahik'          => $this->repository->findWithRelations($mustahik->id),
            'backUrl'           => route('gampong.mustahik.index'),
            'editUrl'           => route('gampong.mustahik.edit', $mustahik),
            'canEdit'           => request()->user()->can('update', $mustahik),
            'canToggle'         => false,
            'canAjukan'         => $canAjukan,
            'pengajuanTahunIni' => $pengajuanTahunIni,
            'senif'             => SubstansiKategori::aktif()->orderBy('nama')->get(['id', 'nama', 'kode_asnaf']),
            'tahunSekarang'     => $tahun,
        ]);
    }

    public function edit(Mustahik $mustahik): Response
    {
        $this->authorize('update', $mustahik);

        return Inertia::render('Mustahik/Edit', [
            'mustahik'  => $mustahik->load(['desa:kode,nama', 'kecamatan:kode,nama', 'kabupaten:kode,nama', 'provinsi:kode,nama']),
            'readonly'  => ['kode_desa', 'kode_kecamatan', 'kode_kabupaten', 'kode_provinsi'],
            'backUrl'   => route('gampong.mustahik.show', $mustahik),
            'mode'      => 'gampong',
        ]);
    }

    public function update(UpdateGampongMustahikRequest $request, Mustahik $mustahik): RedirectResponse
    {
        $this->authorize('update', $mustahik);

        $data             = $request->validated();
        $data['kode_desa']= $mustahik->kode_desa; // lock wilayah

        $this->service->update($mustahik, $data);

        return redirect()
            ->route('gampong.mustahik.show', $mustahik)
            ->with('success', 'Data mustahik berhasil diperbarui.');
    }
}
