<?php

namespace App\Http\Requests\Admin\Permission;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
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
        $id = $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'key' => 'required|string|max:255|unique:permissions,key,' . $id,
            'group' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ];
    }
}
