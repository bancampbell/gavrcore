<?php

namespace App\Http\Requests\Admin\Material;

use Illuminate\Foundation\Http\FormRequest;

class MaterialUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|unique:materials,slug,'.$this->material,
            'content' => 'nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
            'state' => 'sometimes|in:published,draft,archived',
            'access' => 'sometimes|in:public,registered,special',
            'published_at' => 'nullable|date',
            'featured' => 'nullable|boolean',
            'show_on_homepage' => 'nullable|boolean',
            'show_date' => 'nullable|boolean',
            'show_author' => 'nullable|boolean',
            'show_category' => 'nullable|boolean',
            'show_views' => 'nullable|boolean',
            'use_global_settings' => 'nullable|boolean',
        ];
    }
}
