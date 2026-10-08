<?php

use App\Exceptions\InvalidRefreshTokenException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Tymon\JWTAuth\Exceptions\JWTException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: '',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isJwtJson = function (Request $request): bool {
            return $request->expectsJson()
                || $request->is([
                    'login',
                    'register',
                    'refresh',
                    'logout',
                    'me',
                    'profile',
                    'profile/*',
                    'admin/*',
                    'forgot-password',
                    'reset-password',
                ]);
        };

        $exceptions->shouldRenderJsonWhen($isJwtJson);

        $exceptions->render(function (InvalidRefreshTokenException $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($isJwtJson) {
            if ($isJwtJson($request)) {
                return response()->json(['message' => $e->getMessage() ?: 'Unauthenticated.'], 401);
            }
        });

        $exceptions->render(function (JWTException $e, Request $request) use ($isJwtJson) {
            if ($isJwtJson($request)) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });
    })->create();
