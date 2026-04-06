<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'OK',
        'data' => [
            'status' => 'ok',
        ],
        'meta' => [
            'version' => '1.0.0',
            'timestamp' => now()->toISOString(),
        ],
    ]);
});

Route::group(['prefix' => 'auth', 'namespace' => 'App\Http\Controllers\Api'], function () {
    Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register']);
    Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:api');
    Route::post('/refresh', [\App\Http\Controllers\Api\AuthController::class, 'refresh'])->middleware('auth:api');
    Route::get('/me', [\App\Http\Controllers\Api\AuthController::class, 'me'])->middleware('auth:api');
});

