<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * B2 (docs/SECURITY.md): security-заголовки на всех web-ответах.
 * CSP пока в Report-Only — перевести в enforce после периода наблюдения.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // CSP Report-Only: перевести в enforce после периода наблюдения.
        // Источники сверены с кодом: Vite/Inertia — 'self'; Turnstile — challenges.cloudflare.com
        // (script-src + frame-src, CaptchaWidget); тема default — Google Fonts
        // (style-src + font-src); data: — инлайн-SVG в admin.css; 'unsafe-inline' в style-src —
        // :style-биндинги Vue. В dev без TLS не ломает: report-only не блокирует.
        $response->headers->set('Content-Security-Policy-Report-Only', implode('; ', [
            "default-src 'self'",
            "script-src 'self' https://challenges.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
            "img-src 'self' data:",
            "font-src 'self' https://fonts.gstatic.com",
            "connect-src 'self'",
            "frame-src https://challenges.cloudflare.com",
            "frame-ancestors 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]));

        return $response;
    }
}
