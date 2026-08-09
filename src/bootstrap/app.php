<?php

use App\Http\Middleware\ShareMenuMiddleware;
use App\Http\Middleware\CheckCookieConsent;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->web(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
            ShareMenuMiddleware::class,
            CheckCookieConsent::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('login'));

        $middleware->alias([
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'admin' => AdminMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Domain exceptions — structured logging + render
        $exceptions->report(function (\DomainException $e) {
            Log::channel('daily')->error('Domain exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        });

        $exceptions->render(function (\DomainException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*') || $request->is('admin/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        });

        // Invalid argument (DTO validation)
        $exceptions->render(function (\InvalidArgumentException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*') || $request->is('admin/*')) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', $e->getMessage());
        });

        // 429 - Слишком много попыток
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Слишком много попыток. Пожалуйста, подождите ' . $retryAfter . ' секунд.',
                    'retry_after' => $retryAfter,
                ], 429);
            }

            return Inertia::render('Errors/429', [
                'message' => 'Слишком много попыток. Пожалуйста, подождите ' . $retryAfter . ' секунд.',
                'retry_after' => $retryAfter,
            ])->toResponse($request);
        });

        // 404 - Страница не найдена
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Ресурс не найден',
                ], 404);
            }

            return Inertia::render('Errors/NotFound', [
                'status' => 404,
            ])->toResponse($request);
        });

        // 500 - Ошибка сервера
        $exceptions->render(function (\Throwable $e, Request $request) {
            if (!app()->isProduction()) {
                return null;
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Внутренняя ошибка сервера. Пожалуйста, попробуйте позже.',
                ], 500);
            }

            return Inertia::render('Errors/ServerError', [
                'status' => 500,
            ])->toResponse($request);
        });
    })->create();
