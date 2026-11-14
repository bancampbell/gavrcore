<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * A (docs/SECURITY.md): авто-выход из админки/кабинета при простое сессии
 * дольше ADMIN_IDLE_TIMEOUT минут. Гостей не трогает — публичные страницы
 * работают как раньше, а шаг 2FA (метка two_factor_pending у не
 * аутентифицированного пользователя) таймаутом не ломается.
 */
class SessionIdleTimeout
{
    public const SESSION_KEY = 'last_activity_at';

    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $lastActivity = (int) $request->session()->get(self::SESSION_KEY);
        $timeoutSeconds = (int) config('session.idle_timeout', 30) * 60;

        if ($lastActivity > 0 && time() - $lastActivity > $timeoutSeconds) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        $request->session()->put(self::SESSION_KEY, time());

        return $next($request);
    }
}
