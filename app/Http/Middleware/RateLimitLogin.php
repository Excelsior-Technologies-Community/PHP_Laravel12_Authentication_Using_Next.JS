<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class RateLimitLogin
{
    public function handle(Request $request, Closure $next, ?string $type = 'login'): Response
    {
        $key = $type . ':' . ($request->ip() ?: 'global');

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $retryAfter = RateLimiter::availableIn($key);
            return response()->json([
                'success' => false,
                'message' => 'Too many ' . $type . ' attempts. Please try again in ' . ceil($retryAfter / 60) . ' minutes.',
            ], 429);
        }

        RateLimiter::hit($key, 60);

        return $next($request);
    }
}
