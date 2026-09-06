<?php

namespace App\Modules\UserManager\Infrastructure\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // См. RegisterRequest: регистрозависимый unique на PostgreSQL.
        if ($this->filled('email')) {
            $this->merge([
                'email' => mb_strtolower(trim((string) $this->input('email'))),
            ]);
        }
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|regex:/^[a-z0-9_-]{2,50}$/|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'blocked' => 'nullable|boolean',
            'activated' => 'nullable|boolean',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:groups,id',
        ];
    }
}
