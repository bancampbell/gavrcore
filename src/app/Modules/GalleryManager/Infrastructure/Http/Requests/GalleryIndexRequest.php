<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Infrastructure\Http\Requests;

use App\Modules\GalleryManager\Domain\ValueObjects\GalleryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GalleryIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'type' => ['nullable', Rule::in(array_column(GalleryType::cases(), 'value'))],
            'status' => 'nullable|boolean',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }
}
