<?php

use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['service' => 'auth']);
});

Route::get('/docs', function () {
    return response()->view('swagger');
});

Route::get('/docs/openapi.yaml', function () {
    return response()->file(base_path('docs/openapi.yaml'), [
        'Content-Type' => 'application/yaml',
    ]);
});

Route::get('auth/google', [SocialAuthController::class, 'redirect'])
    ->name('auth.google');

Route::get('auth/google/callback', [SocialAuthController::class, 'callback'])
    ->name('auth.google.callback');
