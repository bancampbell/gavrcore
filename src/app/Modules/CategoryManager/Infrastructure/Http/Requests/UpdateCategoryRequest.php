<?php

namespace App\Modules\CategoryManager\Infrastructure\Http\Requests;

use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
        $categoryId = $this->route('id');

        return [
            'name' => 'sometimes|string|max:255|unique:categories,name,'.$categoryId,
            'alias' => 'sometimes|string|min:1|unique:categories,alias,'.$categoryId,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id|not_in:'.$categoryId,
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * @param  \Illuminate\Validation\Validator  $validator
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $categoryId = $this->route('id');
            $parentId = $this->input('parent_id');

            if ($parentId === null || $categoryId === null) {
                return;
            }

            $category = CategoryModel::find($categoryId);
            if (! $category) {
                return;
            }

            $isDescendant = CategoryModel::where('id', $parentId)
                ->where('lft', '>', $category->lft)
                ->where('rgt', '<', $category->rgt)
                ->exists();

            if ($isDescendant) {
                $validator->errors()->add(
                    'parent_id',
                    'Родительская категория не может быть подкатегорией текущей категории.'
                );
            }
        });
    }
}
