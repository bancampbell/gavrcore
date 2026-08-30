<?php

namespace App\Modules\MenuManager\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateMenuTypeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'alias' => 'nullable|string|max:255|unique:menu_types,alias',
            'description' => 'nullable|string',
            'ordering' => 'nullable|integer',
            'status' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Введите название',
            'alias.unique' => 'Тип меню с таким алиасом уже существует',
        ];
    }
}