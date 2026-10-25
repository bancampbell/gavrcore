<?php

namespace App\Modules\CookieConsent\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckCookieConsent
{
    public function handle(Request $request, Closure $next): mixed
    {
        $consent = $request->cookie('cookie_consent');
        $rawCategories = $request->cookie('cookie_consent_categories', '{}');
        $categories = is_string($rawCategories) ? json_decode($rawCategories, true) : null;

        Inertia::share('cookieConsent', [
            'given' => $consent === 'accepted',
            'categories' => [
                'necessary' => true,
                'analytics' => $categories['analytics'] ?? false,
                'marketing' => $categories['marketing'] ?? false,
            ],
        ]);

        return $next($request);
    }
}
