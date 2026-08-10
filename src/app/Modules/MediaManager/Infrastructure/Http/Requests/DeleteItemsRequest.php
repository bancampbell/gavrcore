<?php

namespace Modules\MediaManager\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\MediaManager\Application\DTO\DeleteItemsData;

class DeleteItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'paths' => 'required|array',
            'paths.*' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (str_contains($value, '..')) {
                        $fail('Путь не может содержать переходы к родительской директории.');
                    }
                },
            ],
        ];
    }

    public function toDTO(): DeleteItemsData
    {
        return new DeleteItemsData(
            paths: $this->input('paths'),
        );
    }
}
