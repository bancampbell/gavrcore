<?php

namespace App\Modules\UserManager\Infrastructure\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmTwoFactorRequest extends FormRequest
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
            'code' => 'required|digits:6',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Код обязателен',
            'code.digits' => 'Код должен состоять из 6 цифр',
        ];
    }
}
