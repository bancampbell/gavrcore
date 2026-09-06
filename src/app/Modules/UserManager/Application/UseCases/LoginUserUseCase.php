<?php

namespace App\Modules\UserManager\Application\UseCases;

use App\Modules\UserManager\Application\DTO\LoginData;
use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class LoginUserUseCase
{
    public function execute(LoginData $data, ?string $ip = null): UserModel
    {
        if (! Auth::attempt(['email' => $data->email, 'password' => $data->password])) {
            throw ValidationException::withMessages([
                'email' => ['Неверные учетные данные'],
            ]);
        }

        /** @var UserModel $user */
        $user = Auth::user();

        if ($user->blocked) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => ['Аккаунт заблокирован'],
            ]);
        }

        if (! $user->activated) {
            Auth::logout();

            throw ValidationException::withMessages([
                'email' => ['Аккаунт не активирован'],
            ]);
        }

        // Обновляем время последнего входа и IP
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?? request()->ip(),
        ]);

        return $user->fresh();
    }
}
