<?php

namespace App\Modules\MaterialManager\Infrastructure\Http\Requests;

use App\Modules\MaterialManager\Domain\Entities\Material;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMaterialRequest extends FormRequest
{
    private const RESERVED_SLUGS = [
        'admin', 'api', 'search', 'sitemap', 'storage', 'material', 'category',
        'login', 'register', 'dashboard', 'galleries',
    ];

    public function authorize(): bool
    {
        return $this->user()->can('create', Material::class);
    }

    public function rules(): array
    {
        // Уникальность slug — в рамках категории; безкатегорийный материал
        // проверяется среди прочих безкатегорийных (category_id IS NULL).
        $categoryId = $this->filled('category_id') ? (int) $this->input('category_id') : null;

        return [
            'title' => 'required|string|max:255',
            'slug' => [
                'nullable', 'string', 'max:255',
                Rule::unique('materials', 'slug')->where(
                    fn ($query) => $categoryId !== null
                        ? $query->where('category_id', $categoryId)
                        : $query->whereNull('category_id')
                ),
                Rule::notIn(self::RESERVED_SLUGS),
            ],
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
            'slug.not_in' => 'Адрес \':input\' занят системным',
            'slug.unique' => $this->filled('category_id')
                ? 'В этой категории уже есть материал с таким адресом'
                : 'Материал с таким адресом уже существует',
            'category_id.exists' => 'Выбранная категория не существует',
            'state.in' => 'Некорректный статус',
        ];
    }
}
