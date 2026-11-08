<?php

namespace App\Modules\MenuManager\Infrastructure\Http\Requests;

use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $menuTypeId = $this->route('menuTypeId');

        return [
            'parent_id' => [
                'nullable',
                'exists:menu_items,id',
                function ($attribute, $value, $fail) use ($menuTypeId) {
                    if ($value === null) {
                        return;
                    }
                    $parent = MenuItemModel::find($value);
                    if ($parent && $parent->menu_type_id != $menuTypeId) {
                        $fail('Родительский элемент должен принадлежать выбранному меню.');
                    }
                },
            ],
            'title' => 'required|string|max:255',
            'link_type' => 'required|in:url,material,category,separator,heading,external',
            'link_value' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $this->input('link_type') === 'category'),
                function ($attribute, $value, $fail) {
                    if ($this->input('link_type') !== 'category') {
                        return;
                    }
                    if (! CategoryModel::where('alias', $value)->exists()) {
                        $fail('Категория с таким алиасом не существует.');
                    }
                },
            ],
            'target' => 'nullable|in:_self,_blank',
            'ordering' => 'nullable|integer',
            'status' => 'nullable|boolean',
            'access' => 'nullable|string',
            'language' => 'nullable|string',
            'position' => 'nullable|in:first,after',
            'after_id' => 'nullable|integer|exists:menu_items,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Введите заголовок',
            'link_value.required' => 'Укажите категорию',
        ];
    }
}
