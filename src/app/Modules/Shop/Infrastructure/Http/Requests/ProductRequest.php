<?php

namespace Modules\Shop\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products,sku',
            'status' => 'sometimes|in:draft,published,archived',
            'stock' => 'integer|min:0',
            'category_id' => 'nullable|uuid|exists:categories,id',
        ];
    }
}
