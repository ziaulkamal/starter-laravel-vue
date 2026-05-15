<?php

namespace App\Http\Requests\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
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
            'type'        => ['required', 'in:section,item'],
            'label'       => ['required', 'string', 'max:100', 'unique:menus,label'],
            'icon'        => ['nullable', 'string', 'max:100'],
            'href'        => ['nullable', 'string', 'max:255', 'unique:menus,href'],
            'permission'  => ['nullable', 'string', 'max:100'],
            'order_index' => ['required', 'integer', 'min:0'],
            'is_active'   => ['boolean'],
            'role_ids'    => ['nullable', 'array'],
            'role_ids.*'  => ['integer', 'exists:roles,id'],
        ];
    }
}
