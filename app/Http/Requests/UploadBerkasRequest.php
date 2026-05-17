<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadBerkasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('berkas.upload');
    }

    public function rules(): array
    {
        return [
            'mustahik_id'  => ['required', 'integer', 'exists:mustahik,id'],
            'jenis_berkas' => ['required', Rule::in(['ktp', 'kk', 'surat_keterangan', 'foto_rumah', 'buku_rekening', 'lainnya'])],
            'files'        => ['required', 'array', 'min:1', 'max:5'],
            'files.*'      => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'keterangan'   => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'files.*' => 'berkas',
        ];
    }
}
