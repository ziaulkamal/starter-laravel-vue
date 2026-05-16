<?php

namespace App\Http\Requests\Mtq\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'nama'      => ['sometimes', 'string', 'max:150'],
            'alamat'    => ['sometimes', 'string', 'max:500'],
            'lat'       => ['nullable', 'numeric', 'between:-90,90'],
            'lng'       => ['nullable', 'numeric', 'between:-180,180'],
            'kapasitas' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
