<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMustahikRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('mustahik.create');
    }

    public function rules(): array
    {
        return [
            // Wilayah
            'kode_provinsi'        => ['required', 'string', 'size:2', 'exists:wilayah,kode'],
            'kode_kabupaten'       => ['required', 'string', 'size:5', 'exists:wilayah,kode'],
            'kode_kecamatan'       => ['required', 'string', 'size:8', 'exists:wilayah,kode'],
            'kode_desa'            => ['required', 'string', 'size:13', 'exists:wilayah,kode'],

            // Identitas
            'nama_lengkap'         => ['required', 'string', 'max:200'],
            'tempat_lahir'         => ['required', 'string', 'max:100'],
            'tanggal_lahir'        => ['required', 'date', 'before:today'],
            'jenis_kelamin'        => ['required', Rule::in(['laki-laki', 'perempuan'])],
            'nik'                  => ['nullable', 'string', 'digits:16', 'unique:mustahik,nik'],

            // Alamat
            'alamat_gampong'       => ['required', 'string', 'max:150'],
            'alamat_dusun'         => ['nullable', 'string', 'max:150'],

            // Kontak & Rekening
            'no_hp'                => ['nullable', 'string', 'max:20'],
            'pemilik_rekening'     => ['nullable', 'string', 'max:150'],
            'nomor_rekening'       => ['nullable', 'string', 'max:50'],

            // Pekerjaan & Status
            'pekerjaan'            => ['required', 'string', 'max:100'],
            'status_pernikahan'    => ['required', Rule::in(['kawin', 'belum_kawin', 'janda', 'duda'])],

            // Kondisi Keluarga
            'jumlah_anggota'       => ['required', Rule::in(['tidak_ada', '1-2', '2-5', 'lebih_5'])],
            'jumlah_tanggungan'    => ['required', Rule::in(['tidak_ada', '1-2', '2-5', 'lebih_5'])],

            // Kondisi Ekonomi
            'range_penghasilan'    => ['required', Rule::in(['tidak_ada', 'kurang_1500000', '1500000_2500000', 'lebih_2500000'])],
            'status_pencari_nafkah'=> ['required', Rule::in(['utama', 'sampingan', 'tidak_mencari'])],

            'catatan'              => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kode_desa'     => 'desa/gampong',
            'kode_kecamatan'=> 'kecamatan',
            'nik'           => 'NIK',
            'no_hp'         => 'nomor HP',
        ];
    }
}
