<?php

namespace App\Modules\MenuManager\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMenuItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $menuTypeId = $this->route('menuTypeId');
        return [
            'parent_id' => 'nullable|exists:menu_items,id',
            'title' => 'required|string|max:255',
            'alias' => ['nullable', 'string', 'max:255', Rule::unique('menu_items', 'alias')->where('menu_type_id', $menuTypeId)],
            'link_type' => 'required|in:url,material,separator,heading,external',
            'link_value' => 'nullable|string',
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
            'alias.unique' => 'Пункт меню с таким алиасом уже существует в этом меню',
        ];
    }
}