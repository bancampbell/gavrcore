<?php

namespace Modules\MediaManager\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Modules\MediaManager\Application\DTO\CopyItemData;

class CopyItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'path' => 'required|string',
        ];
    }

    public function toDTO(): CopyItemData
    {
        return new CopyItemData(
            path: $this->input('path'),
        );
    }
}
