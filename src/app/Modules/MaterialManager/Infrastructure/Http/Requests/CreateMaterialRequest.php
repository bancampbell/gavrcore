<?php

namespace App\Modules\MaterialManager\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Modules\MaterialManager\Domain\Entities\Material::class);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:materials,slug',
            'content' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'state' => ['nullable', Rule::in(['published', 'draft', 'archived'])],
            'access' => ['nullable', Rule::in(['public', 'registered', 'special'])],
            'show_on_homepage' => 'nullable|boolean',
            'show_date' => 'nullable|boolean',
            'show_author' => 'nullable|boolean',
            'show_category' => 'nullable|boolean',
            'show_views' => 'nullable|boolean',
            'use_global_settings' => 'nullable|boolean',
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Заголовок обязателен',
            'title.max' => 'Заголовок не может превышать 255 символов',
            'category_id.exists' => 'Выбранная категория не существует',
            'state.in' => 'Некорректный статус',
        ];
    }
}
