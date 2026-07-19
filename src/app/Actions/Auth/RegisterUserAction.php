<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    /**
     * @param  array<string, string>  $data
     */
    public function execute(array $data): User
    {
        // Генерируем username из email (убираем @ и спецсимволы)
        $username = $data['username'] ?? str()->slug(strstr($data['email'], '@', true));

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'username' => $username,
        ]);
    }
}
