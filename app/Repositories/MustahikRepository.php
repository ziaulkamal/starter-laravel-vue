<?php

namespace App\Repositories;

use App\Models\Mustahik;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class MustahikRepository
{
    public function paginate(array $filters = [], ?string $kodeDesa = null, int $perPage = 20): LengthAwarePaginator
    {
        $query = Mustahik::query()
            ->with(['desa:kode,nama', 'kecamatan:kode,nama'])
            ->withCount('berkasFiles')
            ->withCount('pengajuanBantuan');

        // Scope ke gampong jika admin_gampong
        if ($kodeDesa) {
            $query->where('kode_desa', $kodeDesa);
        }

        $this->applyFilters($query, $filters);

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function findWithRelations(int $id): Mustahik
    {
        return Mustahik::with([
            'desa:kode,nama',
            'kecamatan:kode,nama',
            'kabupaten:kode,nama',
            'provinsi:kode,nama',
            'createdBy:id,name',
            'berkasFiles',
            'pengajuanBantuan' => fn ($q) => $q->with([
                'substansiKategori:id,nama,kode_asnaf',
                'diajukanOleh:id,name',
                'diputuskanOleh:id,name',
            ])->orderByDesc('tahun'),
        ])->findOrFail($id);
    }

    public function nikExists(string $nik, ?int $exceptId = null): bool
    {
        return Mustahik::where('nik', $nik)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists();
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($q) => $q
                ->where('nama_lengkap', 'like', "%{$search}%")
                ->orWhere('nik', 'like', "%{$search}%")
            );
        }

        if (!empty($filters['kode_kecamatan'])) {
            $query->where('kode_kecamatan', $filters['kode_kecamatan']);
        }

        if (!empty($filters['kode_desa'])) {
            $query->where('kode_desa', $filters['kode_desa']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['jenis_kelamin'])) {
            $query->where('jenis_kelamin', $filters['jenis_kelamin']);
        }

        if (!empty($filters['range_penghasilan'])) {
            $query->where('range_penghasilan', $filters['range_penghasilan']);
        }
    }
}
