<?php

namespace App\Modules\MaterialManager\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage', \App\Modules\MaterialManager\Domain\Entities\Material::class);
    }

    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:materials,id',
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Выберите хотя бы один материал',
            'ids.min' => 'Выберите хотя бы один материал',
            'ids.array' => 'Некорректный формат данных',
            'ids.*.exists' => 'Один из материалов не существует',
        ];
    }
}
