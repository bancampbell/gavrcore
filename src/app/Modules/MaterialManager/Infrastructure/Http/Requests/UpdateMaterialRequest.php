<?php

namespace App\Modules\MaterialManager\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        if ($user->hasPermission('admin.access') || $user->hasPermission('materials.manage')) {
            return true;
        }

        $materialId = $this->route('id');
        if (!$materialId) {
            return false;
        }

        $model = \App\Modules\MaterialManager\Infrastructure\Models\MaterialModel::find((int)$materialId);

        if (!$model) {
            return false;
        }

        return $user->id === $model->user_id;
    }

    public function rules(): array
    {
        $materialId = $this->route('id');

        return [
            'title' => 'sometimes|required|string|max:255',
            'slug' => "nullable|string|max:255|unique:materials,slug,{$materialId},id",
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
            'category_id.exists' => 'Выбранная категория не существует',
            'state.in' => 'Некорректный статус',
            'template.in' => 'Некорректный шаблон',
        ];
    }
}
