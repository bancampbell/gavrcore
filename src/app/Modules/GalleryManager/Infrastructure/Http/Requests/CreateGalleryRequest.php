<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Infrastructure\Http\Requests;

use App\Modules\GalleryManager\Domain\ValueObjects\GalleryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateGalleryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', Rule::in(array_map(fn ($case) => $case->value, GalleryType::cases()))],
            'settings' => ['nullable', 'array'],
            'status' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('status')) {
            $this->merge(['status' => true]);
        }
    }
}
