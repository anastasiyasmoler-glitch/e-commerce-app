<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class RecordHttpMetrics
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->is('metrics', 'up')) {
            return $response;
        }

        $method = strtoupper($request->getMethod());
        $status = (string) $response->getStatusCode();
        $key = $method.':'.$status;

        $counts = Cache::get('prometheus:http_requests_total', []);
        if (! is_array($counts)) {
            $counts = [];
        }
        $counts[$key] = (int) ($counts[$key] ?? 0) + 1;
        Cache::put('prometheus:http_requests_total', $counts, now()->addDays(7));

        return $response;
    }
}
