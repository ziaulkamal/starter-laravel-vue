<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parent_id'   => ['nullable', 'integer', 'exists:menus,id'],
            'type'        => ['sometimes', 'in:section,item'],
            'label'       => ['sometimes', 'string', 'max:100', Rule::unique('menus', 'label')->ignore($this->route('menu'))],
            'icon'        => ['nullable', 'string', 'max:100'],
            'href'        => ['nullable', 'string', 'max:255', Rule::unique('menus', 'href')->ignore($this->route('menu'))],
            'order_index' => ['sometimes', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
        ];
    }
}
