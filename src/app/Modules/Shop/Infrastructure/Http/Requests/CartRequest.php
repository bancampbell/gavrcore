<?php

namespace Modules\Shop\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'product_id' => 'required|string|uuid',
            'quantity' => 'required|integer|min:1',
        ];
    }
}
