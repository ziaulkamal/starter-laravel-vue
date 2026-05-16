<?php

namespace App\Http\Requests\Mtq\Master;

use App\Models\Kriteria;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateKriteriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'cabang_id' => ['sometimes', 'integer', 'exists:cabangs,id'],
            'nama'      => ['sometimes', 'string', 'max:100'],
            'bobot'     => ['sometimes', 'numeric', 'min:0.01', 'max:100'],
            'min_nilai' => ['sometimes', 'numeric', 'min:0'],
            'max_nilai' => ['sometimes', 'numeric', 'min:0', 'gt:min_nilai'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->has('bobot')) {
                return;
            }

            $kriteria = $this->route('kriteria');
            $cabangId = (int) ($this->input('cabang_id') ?? $kriteria->cabang_id);
            $bobot    = (float) $this->input('bobot', 0);
            $existing = Kriteria::where('cabang_id', $cabangId)
                ->where('id', '!=', $kriteria->id)
                ->sum('bobot');

            if (($existing + $bobot) > 100) {
                $validator->errors()->add(
                    'bobot',
                    'Total bobot kriteria untuk cabang ini akan melebihi 100%. Sisa: ' . (100 - $existing) . '%.'
                );
            }
        });
    }
}
