<?php

namespace App\Modules\FormBuilder\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormStatusRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['status' => 'required|boolean'];
    }
}