<?php

namespace App\Http\Requests\Admin\Material;

use Illuminate\Foundation\Http\FormRequest;

class MaterialStoreRequest extends FormRequest
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
            'alias' => 'nullable|string|unique:materials,alias',
            'content' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'state' => 'nullable|in:published,draft,archived',
            'access' => 'nullable|in:public,registered,special',
            'published_at' => 'nullable|date',
        ];
    }
}
