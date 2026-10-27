<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/** Like Rails `rate_limit to: 10, within: 3.minutes`: counts requests per IP and action. */
class RateLimit
{
    public function handle(Request $request, Closure $next, string $name, string $backTo, int $to = 10, int $within = 180)
    {
        $key = "rate-limit:{$name}:{$request->ip()}";
        if (RateLimiter::tooManyAttempts($key, $to)) {
            return redirect($backTo)->with('alert', 'Tente novamente mais tarde.');
        }
        RateLimiter::hit($key, $within);

        return $next($request);
    }
}
