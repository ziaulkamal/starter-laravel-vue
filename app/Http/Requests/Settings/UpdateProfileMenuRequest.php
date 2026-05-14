<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label'       => ['sometimes', 'string', 'max:100', Rule::unique('profile_menu_items', 'label')->ignore($this->route('profileMenuItem'))],
            'icon'        => ['nullable', 'string', 'max:100'],
            'href'        => ['sometimes', 'string', 'max:255', Rule::unique('profile_menu_items', 'href')->ignore($this->route('profileMenuItem'))],
            'order_index' => ['sometimes', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ];
    }
}
