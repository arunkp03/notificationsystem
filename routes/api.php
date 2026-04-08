<?php

use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.user')->group(function () {
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});
