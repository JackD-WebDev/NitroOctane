<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\Users\OtherBrowserSessionsController;

Route::get('/health', [HealthCheckController::class, 'index']);

Route::middleware(['auth:sanctum'])->group(function() {
    Route::get('/user', [UserController::class, 'getMe']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/user/{id}', [UserController::class, 'findById']);
    Route::post('/user/username', [UserController::class, 'findByUsername']);
    Route::get('/user/email/{email}', [UserController::class, 'findByEmail']);
    Route::get('/sessions', [OtherBrowserSessionsController::class, 'getSessions']);
    Route::delete('/sessions', [OtherBrowserSessionsController::class, 'logoutOtherBrowserSessions']);
});
