<?php

use App\Http\Controllers\NotificationHistoryController;
use Illuminate\Support\Facades\Route;

Route::get('/notifications', [NotificationHistoryController::class, 'index']);
Route::get('/notifications/{id}', [NotificationHistoryController::class, 'show']);
Route::post('/notifications/{id}/replay', [NotificationHistoryController::class, 'replay']);
