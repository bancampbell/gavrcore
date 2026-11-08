<?php

namespace App\Modules\MaterialManager\Infrastructure\Http\Requests;

use App\Modules\MaterialManager\Infrastructure\Models\MaterialModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaterialRequest extends FormRequest
{
    private const RESERVED_SLUGS = [
        'admin', 'api', 'search', 'sitemap', 'storage', 'material', 'category',
        'login', 'register', 'dashboard', 'galleries',
    ];

    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        if ($user->hasPermission('admin.access') || $user->hasPermission('materials.manage')) {
            return true;
        }

        $materialId = $this->route('id');
        if (! $materialId) {
            return false;
        }

        $model = MaterialModel::find((int) $materialId);

        if (! $model) {
            return false;
        }

        return $user->id === $model->user_id;
    }

    public function rules(): array
    {
        $materialId = $this->route('id');
        $categoryId = $this->resolveScopeCategoryId();

        return [
            'title' => 'sometimes|required|string|max:255',
            'slug' => [
                'nullable', 'string', 'max:255',
                Rule::unique('materials', 'slug')
                    ->ignore($materialId)
                    ->where(
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
            'template' => ['nullable', Rule::in(['default', 'warm', 'landing'])],
            'meta_title' => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Заголовок обязателен',
            'slug.not_in' => 'Адрес \':input\' занят системным',
            'slug.unique' => $this->resolveScopeCategoryId() !== null
                ? 'В этой категории уже есть материал с таким адресом'
                : 'Материал с таким адресом уже существует',
            'category_id.exists' => 'Выбранная категория не существует',
            'state.in' => 'Некорректный статус',
            'template.in' => 'Некорректный шаблон',
        ];
    }

    /**
     * Категория, в рамках которой проверяем уникальность slug: переданная
     * в запросе (включая явный null — переезд в корень) либо текущая
     * категория материала. Должна совпадать со скоупом в use case.
     */
    private function resolveScopeCategoryId(): ?int
    {
        if ($this->has('category_id')) {
            $value = $this->input('category_id');

            return $value !== null ? (int) $value : null;
        }

        return MaterialModel::find((int) $this->route('id'))?->category_id;
    }
}
