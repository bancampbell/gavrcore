<?php

namespace App\Modules\UserManager\Infrastructure\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'order' => 'required|array',
            'order.*.id' => 'required|integer|exists:access_levels,id',
            'order.*.ordering' => 'required|integer',
        ];
    }
}
