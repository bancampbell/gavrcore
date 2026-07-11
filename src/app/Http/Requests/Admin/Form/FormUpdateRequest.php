<?php

namespace App\Http\Requests\Admin\Form;

use Illuminate\Foundation\Http\FormRequest;

class FormUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $formId = $this->route('form') ? $this->route('form')->id : null;

        return [
            'title' => 'required|string|max:255',
            'alias' => 'nullable|string|max:255|unique:forms,alias,' . $formId,
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
            'is_dynamic' => 'nullable|boolean',
            'fields' => 'nullable|array',
            'settings' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название обязательно для заполнения',
            'alias.unique' => 'Такой алиас уже существует',
        ];
    }
}
