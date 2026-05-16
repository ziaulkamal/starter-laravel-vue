<?php

namespace App\Http\Requests\Mtq\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreGolonganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'cabang_id'      => ['required', 'integer', 'exists:cabangs,id'],
            'nama'           => ['required', 'string', 'max:100'],
            'min_usia'       => ['nullable', 'integer', 'min:0', 'max:99'],
            'max_usia'       => ['nullable', 'integer', 'min:0', 'max:99', 'gte:min_usia'],
            'jenis_kelamin'  => ['required', 'in:L,P,LK'],
        ];
    }
}
