<?php

namespace App\Modules\UserManager\Infrastructure\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Email в БД хранится в нижнем регистре (нормализация в Email VO).
        // Без этого unique-правило на регистрозависимом PostgreSQL пропускает
        // дубликат с другим регистром, и вставка падает с QueryException (500).
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
            'email' => 'required|string|email|max:255|unique:users',
            'username' => 'nullable|string|regex:/^[a-z0-9_-]{2,50}$/|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Имя обязательно',
            'email.required' => 'Email обязателен',
            'email.email' => 'Некорректный email',
            'email.unique' => 'Email уже зарегистрирован',
            'username.regex' => 'Логин: только латиница в нижнем регистре, цифры, дефис и подчёркивание (2-50 символов)',
            'username.unique' => 'Логин уже занят',
            'password.required' => 'Пароль обязателен',
            'password.min' => 'Пароль должен быть не менее 8 символов',
            'password.confirmed' => 'Пароли не совпадают',
        ];
    }
}
