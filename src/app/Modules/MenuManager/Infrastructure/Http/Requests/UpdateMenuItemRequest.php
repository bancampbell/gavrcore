<?php

namespace App\Modules\MenuManager\Infrastructure\Http\Requests;

use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMenuItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('id');
        $menuTypeId = $this->input('menu_type_id');

        if (! $menuTypeId && $id) {
            $menuTypeId = MenuItemModel::where('id', $id)->value('menu_type_id');
        }

        return [
            'menu_type_id' => 'nullable|integer|exists:menu_types,id',
            'parent_id' => [
                'nullable',
                'integer',
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
            'alias' => ['nullable', 'string', 'max:255', Rule::unique('menu_items', 'alias')->where('menu_type_id', $menuTypeId)->ignore($id)],
            'link_type' => 'required|in:url,material,separator,heading,external',
            'link_value' => 'nullable|string',
            'target' => 'nullable|in:_self,_blank',
            'ordering' => 'nullable|integer',
            'status' => 'nullable|boolean',
            'access' => 'nullable|string',
            'language' => 'nullable|string',
            'position' => 'nullable|in:first,after,keep',
            'after_id' => 'nullable|integer|exists:menu_items,id',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Введите заголовок',
            'alias.unique' => 'Пункт меню с таким алиасом уже существует в этом меню',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('menuTypeId')) {
            $this->merge(['menu_type_id' => $this->route('menuTypeId')]);
        }
    }
}
