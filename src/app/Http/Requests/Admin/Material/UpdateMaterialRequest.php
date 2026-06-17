<?php

namespace App\Http\Requests\Admin\Material;

use App\Models\Material;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialRequest extends FormRequest
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
        /** @var Material|null $material */
        $material = $this->route('material');

        return [
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:materials,slug,'.($material ? $material->id : 'NULL'),
            'content' => 'nullable|string',
            'category_id' => 'sometimes|required|exists:categories,id',
            'state' => 'nullable|in:published,draft,archived',
            'access' => 'nullable|in:public,registered,special',
            'featured' => 'nullable|in:0,1',
            'show_on_homepage' => 'nullable|in:0,1',
            'show_date' => 'nullable|boolean',
            'show_author' => 'nullable|boolean',
            'show_category' => 'nullable|boolean',
            'show_views' => 'nullable|boolean',
            'use_global_settings' => 'nullable|boolean',
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
