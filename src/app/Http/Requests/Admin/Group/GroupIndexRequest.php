<?php

namespace App\Http\Requests\Admin\Group;

use Illuminate\Foundation\Http\FormRequest;

class GroupIndexRequest extends FormRequest
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
            'search' => 'nullable|string',
            'status' => 'nullable|boolean',
            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer',
        ];
    }
}
