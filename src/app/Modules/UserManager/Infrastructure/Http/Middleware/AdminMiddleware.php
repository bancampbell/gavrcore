<?php

namespace App\Modules\UserManager\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
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
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Доступ запрещён'], 403);
            }

            return Inertia::render('Errors/Forbidden', [
                'status' => 403,
            ])->toResponse($request)->setStatusCode(403);
        }

        return $next($request);
    }
}
