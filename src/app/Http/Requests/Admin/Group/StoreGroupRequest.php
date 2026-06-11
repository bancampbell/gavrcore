<?php

namespace App\Http\Requests\Admin\Group;

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
            'alias' => 'nullable|string|max:255|unique:groups',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
            'ordering' => 'nullable|integer',
        ];
    }
}
