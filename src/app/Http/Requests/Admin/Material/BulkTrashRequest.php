<?php

namespace App\Http\Requests\Admin\Material;

use Illuminate\Foundation\Http\FormRequest;

class BulkTrashRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids' => 'required|array',
            'ids.*' => 'exists:materials,id'
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Выберите хотя бы один материал',
            'ids.array' => 'Некорректный формат данных',
            'ids.*.exists' => 'Один из материалов не существует'
        ];
    }
}
