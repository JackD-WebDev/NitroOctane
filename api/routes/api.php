<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\Users\OtherBrowserSessionsController;

Route::get('/health', [HealthCheckController::class, 'index']);
Route::get('/check-unique', function (Request $request) {
    $field = $request->query('field');
    $value = $request->query('value');
    if (! in_array($field, ['username', 'email'])) {
        return response()->json(['unique' => false, 'error' => 'Invalid field'], 400);
    }
    $exists = DB::table('users')->where($field, $value)->exists();

    return response()->json(['unique' => ! $exists]);
});
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [UserController::class, 'getMe']);
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/user/{id}', [UserController::class, 'findById']);
    Route::post('/user/username', [UserController::class, 'findByUsername']);
    Route::get('/user/email/{email}', [UserController::class, 'findByEmail']);
    Route::get('/sessions', [OtherBrowserSessionsController::class, 'getSessions']);
    Route::delete('/sessions', [OtherBrowserSessionsController::class, 'logoutOtherBrowserSessions']);
});
