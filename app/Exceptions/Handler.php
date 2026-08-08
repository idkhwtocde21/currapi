<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (ThrottleRequestsException $e, Request $request) {
            // Return JSON for requests that expect JSON or look like XHR/fetch requests
            if (
                $request->expectsJson() ||
                $request->wantsJson() ||
                $request->is('api/*') ||
                $request->ajax() ||
                $request->header('X-Requested-With') === 'XMLHttpRequest' ||
                str_contains(strtolower($request->header('accept', '')), 'application/json')
            ) {
                $headers = method_exists($e, 'getHeaders') ? $e->getHeaders() : [];
                $retryAfter = null;
                if (is_array($headers) && isset($headers['Retry-After'])) {
                    $retryAfter = is_array($headers['Retry-After']) ? $headers['Retry-After'][0] : $headers['Retry-After'];
                }

                // Persist a global block so other currency endpoints are also blocked for this user/ip
                try {
                    $identifier = $request->user()?->id ?: $request->ip();
                    if ($identifier) {
                        $ttl = is_numeric($retryAfter) ? (int) $retryAfter : 60;
                        $key = "global_block:{$identifier}";
                        Cache::put($key, time() + $ttl, $ttl);
                    }
                } catch (\Throwable $ex) {
                    // ignore cache errors
                }

                return response()->json([
                    'message' => $e->getMessage() ?: 'Too Many Requests',
                    'retry_after' => $retryAfter,
                ], 429);
            }
        });
    }
}
