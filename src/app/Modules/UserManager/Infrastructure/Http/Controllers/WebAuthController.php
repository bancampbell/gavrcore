<?php

namespace App\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\UserManager\Application\DTO\RegisterData;
use App\Modules\UserManager\Application\UseCases\RegisterUserUseCase;
use App\Modules\UserManager\Infrastructure\Http\Requests\Auth\RegisterRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

final class WebAuthController extends Controller
{
    public function __construct(
        private readonly RegisterUserUseCase $registerUser,
    ) {
    }

    /**
     * Страница входа пользователя
     */
    public function showLogin(): Response
    {
        return Inertia::render('UserManager/Auth/Login', [
            'registerUrl' => route('register'),
        ]);
    }

    /**
     * Авторизация пользователя
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Email в БД хранится в нижнем регистре (Email VO). Без нормализации
        // вход с User@Mail.com не находит user@mail.com на PostgreSQL.
        $credentials['email'] = mb_strtolower(trim($credentials['email']));

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Параллельно с LoginUserUseCase: заблокированный или
            // неактивированный аккаунт не должен получить сессию
            if ($user->blocked || ! $user->activated) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => $user->blocked ? 'Аккаунт заблокирован' : 'Аккаунт не активирован',
                ])->withInput();
            }

            $request->session()->regenerate();

            // Проверяем, откуда пришёл пользователь
            $referer = $request->headers->get('referer');

            // Если пришёл со страницы логина или с главной — редирект на главную
            if ($referer && (
                    str_contains($referer, '/login') ||
                    str_contains($referer, '/register') ||
                    $referer === route('home')
                )) {
                return redirect()->route('home');
            }

            // Иначе — туда, куда пытался попасть (или в дашборд)
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Неверный email или пароль',
        ]);
    }

    /**
     * Страница регистрации
     */
    public function showRegister(): Response
    {
        return Inertia::render('UserManager/Auth/Register', [
            'loginUrl' => route('login'),
        ]);
    }

    /**
     * Регистрация пользователя
     */
    public function register(RegisterRequest $request)
    {
        $user = $this->registerUser->execute(RegisterData::fromArray($request->validated()));

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('home');
    }

    /**
     * Выход пользователя
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
