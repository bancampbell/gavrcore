<?php

namespace App\Http\Requests\Admin\Menu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('menuTypeId')) {
            $this->merge([
                'menu_type_id' => $this->route('menuTypeId'),
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id = $this->route('id');
        $menuTypeId = $this->menu_type_id ?? $this->route('menuTypeId');

        $rules = [
            'menu_type_id' => 'required|exists:menu_types,id',
            'parent_id' => 'nullable|exists:menu_items,id',
            'title' => 'required|string|max:255',
            'link_type' => 'required|in:url,material,separator,heading,external',
            'link_value' => 'nullable|string',
            'target' => 'nullable|in:_self,_blank',
            'ordering' => 'nullable|integer',
            'status' => 'nullable|boolean',
            'access' => 'nullable|string',
            'language' => 'nullable|string',
        ];

        // Правило для alias
        if ($this->filled('alias')) {
            $rules['alias'] = [
                'nullable',
                'string',
                'max:255',
                Rule::unique('menu_items', 'alias')
                    ->where('menu_type_id', $menuTypeId)
                    ->ignore($id)
            ];
        } else {
            $rules['alias'] = 'nullable|string|max:255';
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'menu_type_id.required' => 'Выберите тип меню',
            'menu_type_id.exists' => 'Выбранный тип меню не существует',
            'title.required' => 'Введите заголовок',
            'alias.unique' => 'Пункт меню с таким алиасом уже существует в этом меню',
        ];
    }
}
