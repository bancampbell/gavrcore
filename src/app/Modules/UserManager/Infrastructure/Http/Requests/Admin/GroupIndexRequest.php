<?php

namespace App\Modules\UserManager\Infrastructure\Http\Requests\Admin;

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

    protected function prepareForValidation(): void
    {
        if ($this->has('status') && $this->status !== '' && $this->status !== null) {
            $this->merge([
                'status' => filter_var($this->status, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
