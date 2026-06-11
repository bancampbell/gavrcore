<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $id = $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username,'.$id,
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'nullable|string|min:6',
            'blocked' => 'nullable|boolean',
            'activated' => 'nullable|boolean',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:groups,id',
        ];
    }
}
