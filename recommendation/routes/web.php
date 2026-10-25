<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/recommendations/health', [HealthController::class, 'health']);

Route::middleware('gateway.user')->group(function (): void {
    Route::post('/recommendations/chat', [ChatController::class, 'store']);
});
