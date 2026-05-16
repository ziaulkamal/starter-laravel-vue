<?php

namespace App\Http\Requests\Mtq\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGolonganRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'cabang_id'     => ['sometimes', 'integer', 'exists:cabangs,id'],
            'nama'          => ['sometimes', 'string', 'max:100'],
            'min_usia'      => ['nullable', 'integer', 'min:0', 'max:99'],
            'max_usia'      => ['nullable', 'integer', 'min:0', 'max:99', 'gte:min_usia'],
            'jenis_kelamin' => ['sometimes', 'in:L,P,LK'],
        ];
    }
}
