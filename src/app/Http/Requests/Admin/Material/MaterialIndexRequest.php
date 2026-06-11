<?php

namespace App\Http\Requests\Admin\Material;

use Illuminate\Foundation\Http\FormRequest;

class MaterialIndexRequest extends FormRequest
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
            'search' => 'nullable|string|max:255',
            'state' => 'nullable|in:published,draft,archived',
            'category_id' => 'nullable|exists:categories,id',
            'access' => 'nullable|in:public,registered,special',
            'author' => 'nullable|exists:users,id',
            'page' => 'nullable|integer|min:1',
        ];
    }
}
