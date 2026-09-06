<?php

namespace App\Modules\UserManager\Infrastructure\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGroupRequest extends FormRequest
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
            'alias' => 'nullable|string|max:255|regex:/^[a-z0-9-]+$/|unique:groups',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
            'ordering' => 'nullable|integer',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ];
    }
}
