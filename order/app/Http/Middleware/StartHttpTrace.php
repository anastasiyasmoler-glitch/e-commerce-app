<?php

namespace App\Http\Middleware;

use App\Tracing\Tracer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StartHttpTrace
{
    public function __construct(
        private readonly Tracer $tracer,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('metrics', 'up')) {
            return $next($request);
        }

        $this->tracer->extractTraceparent($request->headers->get('traceparent'));

        return $this->tracer->run(
            'HTTP '.$request->method().' /'.$request->path(),
            fn (): Response => $next($request),
        );
    }
}
