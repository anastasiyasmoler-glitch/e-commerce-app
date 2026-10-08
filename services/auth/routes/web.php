<?php

use App\Http\Controllers\SocialAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['service' => 'auth']);
});

Route::get('auth/google', [SocialAuthController::class, 'redirect'])
    ->name('auth.google');

Route::get('auth/google/callback', [SocialAuthController::class, 'callback'])
    ->name('auth.google.callback');
