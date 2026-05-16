<?php

namespace App\Http\Requests\Mtq\Master;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreVenueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'nama'      => ['required', 'string', 'max:150'],
            'alamat'    => ['required', 'string', 'max:500'],
            'lat'       => ['nullable', 'numeric', 'between:-90,90'],
            'lng'       => ['nullable', 'numeric', 'between:-180,180'],
            'kapasitas' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
