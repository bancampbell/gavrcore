<?php

namespace App\Modules\MenuManager\Infrastructure\Http\Middleware;

use App\Modules\MenuManager\Application\UseCases\GetMenuTreeUseCase;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class ShareMenuMiddleware
{
    public function __construct(private GetMenuTreeUseCase $getMenuTreeUseCase) {}

    public function handle(Request $request, Closure $next): Response
    {
        Inertia::share('mainMenu', $this->getMenuTreeUseCase->execute('main-menu'));
        return $next($request);
    }
}