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
    ) {}

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

            // Параллельно с LoginUserUseCase: фиксируем время и IP входа
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            // Пароль верный, но включён TOTP: сессию не выдаём. Кладём метку
            // ожидания второго фактора и ведём на страницу ввода кода (общая
            // для админки и кабинета — см. TwoFactorChallengeController).
            if ($user->totp_enabled) {
                Auth::logout();
                $request->session()->regenerate();

                $request->session()->put(TwoFactorChallengeController::SESSION_KEY, [
                    'user_id' => $user->id,
                    'guard' => 'web',
                    'remember' => $request->boolean('remember'),
                    'redirect' => $this->redirectAfterLoginUrl($request),
                ]);

                return redirect()->route('2fa.verify');
            }

            $request->session()->regenerate();

            return redirect($this->redirectAfterLoginUrl($request));
        }

        return back()->withErrors([
            'email' => 'Неверный email или пароль',
        ]);
    }

    /**
     * Куда вести после успешного логина: со страницы логина/регистрации
     * или с главной — на главную, иначе — туда, куда пытался попасть (или в кабинет)
     */
    private function redirectAfterLoginUrl(Request $request): string
    {
        $referer = $request->headers->get('referer');

        if ($referer && (
            str_contains($referer, '/login') ||
            str_contains($referer, '/register') ||
            $referer === route('home')
        )) {
            return route('home');
        }

        return redirect()->intended('/dashboard')->getTargetUrl();
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
