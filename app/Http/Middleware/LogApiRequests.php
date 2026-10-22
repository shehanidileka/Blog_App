<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);

        $response = $next($request);

        $durationMs = (microtime(true) - $start) * 1000;

        $body = method_exists($response, 'getContent')
            ? $response->getContent()
            : null;

        if (is_string($body) && strlen($body) > 20000) {
            $body = substr($body, 0, 20000).'…[truncated]';
        }

        logApiRequest(
            request: $request,
            statusCode: $response->getStatusCode(),
            responseBody: $body,
            durationMs: $durationMs,
        );

        return $response;
    }
}