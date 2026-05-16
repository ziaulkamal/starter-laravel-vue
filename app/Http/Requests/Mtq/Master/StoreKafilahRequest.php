<?php

namespace App\Http\Requests\Mtq\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreKafilahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'nama_kabupaten'      => ['required', 'string', 'max:100'],
            'kode_wilayah'        => ['required', 'string', 'max:10', 'unique:kafilahs,kode_wilayah'],
            'koordinator_nama'    => ['nullable', 'string', 'max:100'],
            'koordinator_kontak'  => ['nullable', 'string', 'max:20'],
        ];
    }
}
