<?php

namespace App\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserManager\Application\DTO\LoginData;
use App\Modules\UserManager\Application\UseCases\LoginUserUseCase;
use App\Modules\UserManager\Infrastructure\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

final class AdminAuthController extends Controller
{
    public function __construct(
        private readonly LoginUserUseCase $loginUser,
    ) {}

    /**
     * Показать страницу входа в админку
     */
    public function create(): Response
    {
        return Inertia::render('UserManager/Auth/AdminLogin');
    }

    /**
     * Авторизация в админке
     */
    public function login(LoginRequest $request)
    {
        try {
            $user = $this->loginUser->execute(
                LoginData::fromArray($request->only('email', 'password')),
                $request->ip()
            );

            // Не-админов отсекаем сразу: до AdminMiddleware они не должны
            // получать сессию и попадать внутрь админки
            if (! $user->isAdmin()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => ['Недостаточно прав для входа в админку'],
                ]);
            }

            $request->session()->regenerate();

            activity()
                ->causedBy($user)
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log('Вход в админку');

            return redirect('/admin/dashboard');
        } catch (ValidationException $e) {
            // Форма входа единственная, кто постит сюда: любой отказ возвращает
            // на страницу логина админки с ошибкой
            return redirect('/admin/login')->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Выход из админки
     */
    public function logout(Request $request)
    {
        if (auth()->check()) {
            activity()
                ->causedBy(auth()->user())
                ->log('Выход из админки');
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
