<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGampongMustahikRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('mustahik.create');
    }

    public function rules(): array
    {
        return [
            'kode_provinsi'     => ['required', 'string', 'size:2', 'exists:wilayah,kode'],
            'kode_kabupaten'    => ['required', 'string', 'size:5', 'exists:wilayah,kode'],
            'kode_kecamatan'    => ['required', 'string', 'size:8', 'exists:wilayah,kode'],
            'kode_desa'         => ['required', 'string', 'size:13', 'exists:wilayah,kode'],
            'nama_lengkap'      => ['required', 'string', 'max:200'],
            'tempat_lahir'      => ['required', 'string', 'max:100'],
            'tanggal_lahir'     => ['required', 'date', 'before:today'],
            'jenis_kelamin'     => ['required', Rule::in(['laki-laki', 'perempuan'])],
            'nik'               => ['nullable', 'string', 'digits:16', 'unique:mustahik,nik'],
            'alamat_gampong'    => ['required', 'string', 'max:150'],
            'alamat_dusun'      => ['nullable', 'string', 'max:150'],
            'no_hp'             => ['nullable', 'string', 'max:20'],
            'pekerjaan'         => ['required', 'string', 'max:100'],
            'status_pernikahan' => ['required', Rule::in(['kawin', 'belum_kawin', 'janda', 'duda'])],
        ];
    }

    public function attributes(): array
    {
        return [
            'kode_desa'      => 'desa/gampong',
            'kode_kecamatan' => 'kecamatan',
            'nik'            => 'NIK',
            'no_hp'          => 'nomor HP',
        ];
    }
}
