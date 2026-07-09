<?php

namespace App\Http\Requests\Admin\Material;

use Illuminate\Foundation\Http\FormRequest;

class MaterialStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:materials,slug',
            'content' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'state' => 'nullable|in:published,draft,archived',
            'access' => 'nullable|in:public,registered,special',
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
