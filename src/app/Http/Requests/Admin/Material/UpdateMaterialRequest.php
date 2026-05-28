<?php

namespace App\Http\Requests\Admin\Material;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $material = $this->route('material');
        return [
            'title' => 'required|string|max:255',
            'alias' => 'nullable|string|max:255|unique:materials,alias,' . ($material ? $material->id : 'NULL'),
            'content' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'state' => 'nullable|in:published,draft,archived',
            'access' => 'nullable|in:public,registered,special',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название обязательно',
            'category_id.required' => 'Категория обязательна',
            'category_id.exists' => 'Выбранная категория не существует',
        ];
    }
}
