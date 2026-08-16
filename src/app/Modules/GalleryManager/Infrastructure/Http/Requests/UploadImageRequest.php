<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'max:10240'],
            'title' => ['nullable', 'string', 'max:255'],
        ];
    }
}
