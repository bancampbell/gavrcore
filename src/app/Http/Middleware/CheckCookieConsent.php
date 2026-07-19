<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckCookieConsent
{
    public function handle(Request $request, Closure $next)
    {
        $consent = $request->cookie('cookie_consent');
        $categories = json_decode($request->cookie('cookie_consent_categories', '{}'), true);

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
