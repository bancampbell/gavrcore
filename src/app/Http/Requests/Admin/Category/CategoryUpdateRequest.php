<?php

namespace App\Http\Requests\Admin\Category;

use Illuminate\Foundation\Http\FormRequest;

class CategoryUpdateRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255|unique:categories,name,'.$this->category,
            'alias' => 'sometimes|string|unique:categories,alias,'.$this->category,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'is_active' => 'nullable|boolean',
        ];
    }
}
