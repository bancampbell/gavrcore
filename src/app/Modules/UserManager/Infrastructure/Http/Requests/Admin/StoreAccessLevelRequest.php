<?php

namespace App\Modules\UserManager\Infrastructure\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccessLevelRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'alias' => 'nullable|string|max:255|regex:/^[a-z0-9-]+$/|unique:access_levels',
            'description' => 'nullable|string',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:groups,id',
            'status' => 'nullable|boolean',
        ];
    }
}
