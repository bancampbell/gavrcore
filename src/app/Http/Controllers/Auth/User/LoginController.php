<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LoginController extends Controller
{
    public function create()
    {
        return Inertia::render('Auth/Login', [
            'registerUrl' => route('register'),
        ]);
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
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

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
