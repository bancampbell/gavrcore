<?php

namespace App\Http\Middleware;

use App\Services\MenuService;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ShareMenuMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $menuService = app(MenuService::class);
        Inertia::share('mainMenu', $menuService->getMenuTree('main-menu'));

        return $next($request);
    }
}
