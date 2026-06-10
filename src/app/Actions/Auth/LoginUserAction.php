<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginUserAction
{
    /**
     * @param array<string, mixed> $credentials
     */
    public function execute(array $credentials, ?Request $request = null): User
    {
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные'],
            ]);
        }

        $user = Auth::user();

        // Обновляем время последнего входа и IP
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request ? $request->ip() : request()->ip(),
        ]);

        return $user->fresh();
    }
}
