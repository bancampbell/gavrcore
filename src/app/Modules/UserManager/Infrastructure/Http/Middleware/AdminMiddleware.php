<?php

namespace App\Modules\UserManager\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('home');
        }

        $user = Auth::user();

        // Повторная проверка статуса на каждый запрос: блокировка/деактивация
        // должны отзывать доступ немедленно, а не только на следующем логине.
        if ($user->blocked || ! $user->activated) {
            Auth::guard('web')->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return $request->expectsJson()
                ? response()->json(['message' => 'Аккаунт заблокирован или деактивирован'], 403)
                : redirect()->route('admin.login');
        }

        if (! $user->isAdmin()) {
            // ТИХИЙ редирект на главную без сообщения
            return redirect()->route('home');
        }

        return $next($request);
    }
}
