<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;

class GlobalBlockMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $identifier = $request->user()?->id ?: $request->ip();
        if (!$identifier) {
            return $next($request);
        }

        $key = "global_block:{$identifier}";
        $blockedUntil = Cache::get($key);

        if ($blockedUntil && is_numeric($blockedUntil)) {
            $now = time();
            if ($blockedUntil > $now) {
                $remaining = $blockedUntil - $now;

                // For explicit AJAX / XHR requests, return a predictable JSON payload (200) the frontend can use
                if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'blocked' => true,
                        'message' => 'Temporarily blocked due to previous excessive requests.',
                        'retry_after' => $remaining,
                    ], 200);
                }

                // For normal navigations, allow the request to proceed but attach a cookie indicating the blocked-until timestamp
                $response = $next($request);
                try {
                    $minutes = (int) ceil($remaining / 60);
                    $cookie = cookie('global_block_until', (string) $blockedUntil, $minutes);
                    return $response->withCookie($cookie);
                } catch (\Throwable $ex) {
                    return $response;
                }
            }
        }

        return $next($request);
    }
}
