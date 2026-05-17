<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMustahikRequest;
use App\Http\Requests\UpdateMustahikRequest;
use App\Models\Mustahik;
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

        $filters = $request->only(['search', 'kode_kecamatan', 'kode_desa', 'status', 'jenis_kelamin', 'range_penghasilan']);

        return Inertia::render('Admin/Mustahik/Index', [
            'mustahik'   => $this->repository->paginate($filters, perPage: 20),
            'filters'    => $filters,
            'kecamatan'  => Wilayah::abdya()->kecamatan()->orderBy('nama')->get(['kode', 'nama']),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Mustahik::class);

        return Inertia::render('Admin/Mustahik/Create', [
            'kecamatan' => Wilayah::abdya()->kecamatan()->orderBy('nama')->get(['kode', 'nama']),
        ]);
    }

    public function store(StoreMustahikRequest $request): RedirectResponse
    {
        $mustahik = $this->service->create($request->validated(), $request->user());

        return redirect()
            ->route('admin.mustahik.show', $mustahik)
            ->with('success', 'Data mustahik berhasil ditambahkan.');
    }

    public function show(Mustahik $mustahik): Response
    {
        $this->authorize('view', $mustahik);

        return Inertia::render('Mustahik/Show', [
            'mustahik'          => $this->repository->findWithRelations($mustahik->id),
            'backUrl'           => route('admin.mustahik.index'),
            'editUrl'           => route('admin.mustahik.edit', $mustahik),
            'canEdit'           => request()->user()->can('update', $mustahik),
            'canToggle'         => request()->user()->can('nonaktifkan', $mustahik),
            'canAjukan'         => false,
            'pengajuanTahunIni' => null,
            'senif'             => [],
            'tahunSekarang'     => now()->year,
        ]);
    }

    public function edit(Mustahik $mustahik): Response
    {
        $this->authorize('update', $mustahik);

        return Inertia::render('Mustahik/Edit', [
            'mustahik'  => $mustahik->load(['desa:kode,nama', 'kecamatan:kode,nama', 'kabupaten:kode,nama', 'provinsi:kode,nama']),
            'kecamatan' => Wilayah::abdya()->kecamatan()->orderBy('nama')->get(['kode', 'nama']),
            'backUrl'   => route('admin.mustahik.show', $mustahik),
        ]);
    }

    public function update(UpdateMustahikRequest $request, Mustahik $mustahik): RedirectResponse
    {
        $this->authorize('update', $mustahik);

        $this->service->update($mustahik, $request->validated());

        return redirect()
            ->route('admin.mustahik.show', $mustahik)
            ->with('success', 'Data mustahik berhasil diperbarui.');
    }

    public function nonaktifkan(Mustahik $mustahik): RedirectResponse
    {
        $this->authorize('nonaktifkan', $mustahik);

        $this->service->nonaktifkan($mustahik);

        return back()->with('success', 'Mustahik berhasil dinonaktifkan.');
    }

    public function aktifkan(Mustahik $mustahik): RedirectResponse
    {
        $this->authorize('nonaktifkan', $mustahik);

        $this->service->aktifkan($mustahik);

        return back()->with('success', 'Mustahik berhasil diaktifkan.');
    }
}
