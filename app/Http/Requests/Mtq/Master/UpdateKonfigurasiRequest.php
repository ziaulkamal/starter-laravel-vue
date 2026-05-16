<?php

namespace App\Http\Requests\Mtq\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKonfigurasiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'tahun'            => ['required', 'integer', 'min:2000', 'max:2100'],
            'tema'             => ['required', 'string', 'max:255'],
            'tgl_mulai_daftar' => ['required', 'date'],
            'tgl_tutup_daftar' => ['required', 'date', 'after_or_equal:tgl_mulai_daftar'],
            'tgl_pelaksanaan'  => ['required', 'date', 'after_or_equal:tgl_tutup_daftar'],
        ];
    }
}
