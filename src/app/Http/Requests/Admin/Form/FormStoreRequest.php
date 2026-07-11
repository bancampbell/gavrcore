<?php

namespace App\Http\Requests\Admin\Form;

use Illuminate\Foundation\Http\FormRequest;

class FormStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'alias' => 'nullable|string|max:255|unique:forms,alias',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
            'is_dynamic' => 'nullable|boolean',
            'fields' => 'nullable|array',
            'settings' => 'nullable|array',
            'notification_emails' => 'nullable|array',
            'notification_emails.*' => 'nullable|email',
        ];
    }
}
