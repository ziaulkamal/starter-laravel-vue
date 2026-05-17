<?php

namespace App\Http\Requests\Mtq\Peserta;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePesertaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'kafilah_id'    => [$this->user()->hasRole('user') ? 'nullable' : 'required', 'integer', 'exists:kafilahs,id'],
            'cabang_id'     => ['required', 'integer', 'exists:cabangs,id'],
            'golongan_id'   => ['required', 'integer', 'exists:golongans,id'],
            'nama'          => ['required', 'string', 'max:100'],
            'nik'           => ['nullable', 'string', 'digits:16'],
            'tempat_lahir'  => ['nullable', 'string', 'max:100'],
            'tgl_lahir'     => ['nullable', 'date'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'alamat'             => ['nullable', 'string', 'max:500'],
            'kode_wilayah_desa'  => ['nullable', 'string', 'max:13', 'exists:wilayah,kode'],
            'foto'               => ['nullable', 'image', 'max:2048'],
        ];
    }
}
