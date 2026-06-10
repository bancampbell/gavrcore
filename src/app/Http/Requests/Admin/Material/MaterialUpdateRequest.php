<?php

namespace App\Http\Requests\Admin\Material;

use Illuminate\Foundation\Http\FormRequest;

class MaterialUpdateRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'alias' => 'sometimes|string|unique:materials,alias,' . $this->material,
            'content' => 'nullable|string',
            'category_id' => 'sometimes|exists:categories,id',
            'state' => 'sometimes|in:published,draft,archived',
            'access' => 'sometimes|in:public,registered,special',
            'published_at' => 'nullable|date',
        ];
    }
}
