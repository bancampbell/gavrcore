<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('home');
        }

        if (!Auth::user()->isAdmin()) {
            // ТИХИЙ редирект на главную без сообщения
            return redirect()->route('home');
        }

        return $next($request);
    }
}
