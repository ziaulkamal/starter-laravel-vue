<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfileMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'label'       => ['required', 'string', 'max:100', 'unique:profile_menu_items,label'],
            'icon'        => ['nullable', 'string', 'max:100'],
            'href'        => ['required', 'string', 'max:255', 'unique:profile_menu_items,href'],
            'order_index' => ['required', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ];
    }
}
