<?php

use App\Models\ApiRequestLog;
use Illuminate\Http\Request;

if (! function_exists('logApiRequest')) {
    /**
     * Log an incoming API request and its response to the database.
     */
    function logApiRequest(Request $request, int $statusCode, ?string $responseBody = null, ?float $durationMs = null): void
    {
        try {
            ApiRequestLog::create([
                'method' => $request->method(),
                'path' => $request->path(),
                'full_url' => $request->fullUrl(),
                'status_code' => $statusCode,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => $request->user()?->id,
                'request_headers' => json_encode($request->headers->all()),
                'request_body' => json_encode($request->except(['password', 'password_confirmation', 'token'])),
                'response_body' => $responseBody,
                'duration_ms' => $durationMs !== null ? (int) round($durationMs) : null,
            ]);
        } catch (Throwable) {
            // Never let logging break the actual request.
        }
    }
}