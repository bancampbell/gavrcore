<?php

namespace App\Modules\MenuManager\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderingRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'order' => 'required|array',
            'order.*.id' => 'required|integer',
            'order.*.ordering' => 'required|integer',
        ];
    }
}