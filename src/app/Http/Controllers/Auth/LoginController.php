<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LoginUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function __construct(
        protected LoginUserAction $loginAction
    ) {
    }

    /**
     * Авторизация через Inertia
     */
    public function login(LoginRequest $request)
    {
        try {
            $user = $this->loginAction->execute($request->only('email', 'password'), $request);

            // Региенерируем сессию для безопасности
            $request->session()->regenerate();

            // Редирект на дашборд
            return redirect()->intended('/admin/dashboard');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Возвращаем ошибки валидации обратно на страницу логина
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Выход из системы
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
