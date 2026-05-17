<?php

namespace App\Services;

use App\Models\Mustahik;
use App\Models\User;
use App\Repositories\MustahikRepository;
use Illuminate\Validation\ValidationException;

class MustahikService
{
    public function __construct(
        private MustahikRepository $repository
    ) {}

    public function create(array $data, User $createdBy): Mustahik
    {
        if (!empty($data['nik']) && $this->repository->nikExists($data['nik'])) {
            throw ValidationException::withMessages([
                'nik' => 'NIK sudah terdaftar dalam sistem.',
            ]);
        }

        // Auto-derive parent wilayah codes dari kode_desa
        $data = $this->deriveWilayahCodes($data);

        return Mustahik::create([
            ...$data,
            'created_by' => $createdBy->id,
            'status'     => 'aktif',
        ]);
    }

    public function update(Mustahik $mustahik, array $data): Mustahik
    {
        if (!empty($data['nik']) && $this->repository->nikExists($data['nik'], $mustahik->id)) {
            throw ValidationException::withMessages([
                'nik' => 'NIK sudah terdaftar dalam sistem.',
            ]);
        }

        $data = $this->deriveWilayahCodes($data);

        $mustahik->update($data);

        return $mustahik->fresh();
    }

    public function nonaktifkan(Mustahik $mustahik): Mustahik
    {
        $mustahik->update(['status' => 'nonaktif']);
        return $mustahik;
    }

    public function aktifkan(Mustahik $mustahik): Mustahik
    {
        $mustahik->update(['status' => 'aktif']);
        return $mustahik;
    }

    /**
     * Derive kode_provinsi, kode_kabupaten, kode_kecamatan dari kode_desa (13 chars)
     * kode_desa = "11.12.01.2006" → provinsi="11", kabupaten="11.12", kecamatan="11.12.01"
     */
    private function deriveWilayahCodes(array $data): array
    {
        if (!empty($data['kode_desa']) && strlen($data['kode_desa']) === 13) {
            $kode = $data['kode_desa'];
            $data['kode_provinsi']  = substr($kode, 0, 2);
            $data['kode_kabupaten'] = substr($kode, 0, 5);
            $data['kode_kecamatan'] = substr($kode, 0, 8);
        }

        return $data;
    }
}
