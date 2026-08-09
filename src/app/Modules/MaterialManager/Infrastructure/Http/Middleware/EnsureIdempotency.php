<?php

namespace App\Modules\MaterialManager\Infrastructure\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdempotency
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key');

        if (!$key || $request->isMethodSafe()) {
            return $next($request);
        }

        $userId = $request->user()?->id ?? 'guest:' . $request->ip();
        $cacheKey = 'idempotency:' . sha1($key . ':' . $userId);

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);

            return response()->json(
                $cached['body'],
                $cached['status'],
                ['X-Idempotency-Replay' => 'true']
            );
        }

        $response = $next($request);

        if ($response->isSuccessful()) {
            Cache::put($cacheKey, [
                'body' => json_decode($response->getContent(), true),
                'status' => $response->getStatusCode(),
            ], 86400);
        }

        return $response;
    }
}
