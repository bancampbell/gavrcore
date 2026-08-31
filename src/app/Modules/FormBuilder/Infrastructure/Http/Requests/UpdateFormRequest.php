<?php

namespace App\Modules\FormBuilder\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $formId = $this->route('form');
        return [
            'title' => 'required|string|max:255',
            'alias' => 'nullable|string|max:255|unique:forms,alias,' . $formId,
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
            'is_dynamic' => 'nullable|boolean',
            'fields' => 'nullable|array',
            'settings' => 'nullable|array',
            'notification_emails' => 'nullable|array',
            'notification_emails.*' => 'nullable|email',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Название обязательно для заполнения',
            'alias.unique' => 'Такой алиас уже существует',
            'notification_emails.*.email' => 'Один из email-адресов имеет неверный формат',
        ];
    }
}