<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/recommendations/health'));

Route::get('/docs', function () {
    return response()->file(public_path('docs/index.html'));
});

Route::get('/docs/openapi.yaml', function () {
    return response()->file(base_path('docs/openapi.yaml'), [
        'Content-Type' => 'application/yaml',
    ]);
});

Route::get('/recommendations/health', [HealthController::class, 'health']);

Route::middleware('gateway.user')->group(function (): void {
    Route::post('/recommendations/chat', [ChatController::class, 'store'])
        ->middleware('throttle:recommendation-chat');
    Route::get('/recommendations/conversations/{id}', [ConversationController::class, 'show']);
    Route::delete('/recommendations/conversations/{id}', [ConversationController::class, 'destroy']);
});
