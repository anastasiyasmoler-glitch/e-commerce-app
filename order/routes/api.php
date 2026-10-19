<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/up', fn () => response()->json(['status' => 'ok']));

Route::middleware('gateway.user')->group(function (): void {
    Route::post('/orders', [OrderController::class, 'store']);
});
