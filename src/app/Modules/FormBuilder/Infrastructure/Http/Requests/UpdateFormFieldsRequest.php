<?php

namespace App\Modules\FormBuilder\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFormFieldsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['fields' => 'required|array'];
    }
}