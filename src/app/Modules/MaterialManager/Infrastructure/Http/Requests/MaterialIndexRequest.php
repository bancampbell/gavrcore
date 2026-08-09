<?php

namespace App\Modules\MaterialManager\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaterialIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('viewAny', \App\Modules\MaterialManager\Domain\Entities\Material::class);
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'state' => ['nullable', Rule::in(['published', 'draft', 'archived'])],
            'category_id' => 'nullable|exists:categories,id',
            'access' => ['nullable', Rule::in(['public', 'registered', 'special'])],
            'author' => 'nullable|exists:users,id',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'sort' => ['nullable', Rule::in(['id', 'title', 'views', 'created_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
