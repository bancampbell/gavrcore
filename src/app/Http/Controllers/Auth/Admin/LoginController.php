<?php

namespace App\Http\Controllers\Auth\Admin;

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
     * Показать страницу входа в админку
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Auth/Login');
    }

    /**
     * Авторизация в админке
     */
    public function login(LoginRequest $request)
    {
        try {
            $user = $this->loginAction->execute($request->only('email', 'password'), $request);

            $request->session()->regenerate();

            activity()
                ->causedBy($user)
                ->withProperties([
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ])
                ->log('Вход в админку');

            return redirect('/admin/dashboard');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
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
