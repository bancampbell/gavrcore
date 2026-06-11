<?php

namespace App\Http\Requests\Admin\Permission;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:permissions',
            'group' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
