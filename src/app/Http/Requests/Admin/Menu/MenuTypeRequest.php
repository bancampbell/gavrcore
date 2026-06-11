<?php

namespace App\Http\Requests\Admin\Menu;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MenuTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'title' => 'required|string|max:255',
            'alias' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('menu_types', 'alias')->ignore($id),
            ],
            'description' => 'nullable|string',
            'ordering' => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Введите название',
            'alias.unique' => 'Тип меню с таким алиасом уже существует',
        ];
    }
}
