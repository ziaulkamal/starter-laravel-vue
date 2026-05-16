<?php

namespace App\Http\Requests\Mtq\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKafilahRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'nama_kabupaten'     => ['sometimes', 'string', 'max:100'],
            'kode_wilayah'       => ['sometimes', 'string', 'max:10', Rule::unique('kafilahs', 'kode_wilayah')->ignore($this->route('kafilah'))],
            'koordinator_nama'   => ['nullable', 'string', 'max:100'],
            'koordinator_kontak' => ['nullable', 'string', 'max:20'],
        ];
    }
}
