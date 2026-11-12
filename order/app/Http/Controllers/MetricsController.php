<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class MetricsController
{
    public function __invoke(): Response
    {
        $service = $this->label(config('app.name', 'auth'));
        $counts = Cache::get('prometheus:http_requests_total', []);
        if (! is_array($counts)) {
            $counts = [];
        }

        $lines = [
            '# HELP app_up 1 if this service can serve Prometheus metrics.',
            '# TYPE app_up gauge',
            'app_up{service="'.$service.'"} 1',
            '# HELP app_memory_bytes PHP memory in use.',
            '# TYPE app_memory_bytes gauge',
            'app_memory_bytes{service="'.$service.'"} '.memory_get_usage(true),
            '# HELP http_requests_total HTTP requests handled by this process group.',
            '# TYPE http_requests_total counter',
        ];

        foreach ($counts as $key => $value) {
            if (! is_string($key) || ! str_contains($key, ':')) {
                continue;
            }
            [$method, $status] = explode(':', $key, 2);
            $lines[] = sprintf(
                'http_requests_total{service="%s",method="%s",status="%s"} %d',
                $service,
                $this->label($method),
                $this->label($status),
                (int) $value,
            );
        }

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]);
    }

    private function label(string $value): string
    {
        return preg_replace('/[^A-Za-z0-9_.:-]/', '_', $value) ?? 'app';
    }
}
