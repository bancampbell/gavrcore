<?php

namespace App\Http\Requests\Admin\Material;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
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
            'slug' => 'nullable|string|max:255|unique:materials,slug',
            'content' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'state' => 'nullable|in:published,draft,archived',
            'access' => 'nullable|in:public,registered,special',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Название обязательно',
            'category_id.required' => 'Категория обязательна',
            'category_id.exists' => 'Выбранная категория не существует',
        ];
    }
}
