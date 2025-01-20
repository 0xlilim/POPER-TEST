<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AccessLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // in milliseconds

        $logData = [
            'timestamp' => now()->toIso8601String(),
            'start_time' => date('Y-m-d H:i:s', (int)$startTime),
            'end_time' => date('Y-m-d H:i:s', (int)$endTime),
            'duration_ms' => $duration,
            'trace_id' => $request->header('X-Amzn-Trace-Id', 'N/A'),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'status_code' => $response->getStatusCode(),
            'user_agent' => $request->userAgent(),
        ];

        Log::channel('access')->info(json_encode($logData));

        return $response;
    }
}