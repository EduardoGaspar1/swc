<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\EnlaceController;
use App\Http\Controllers\Api\NetworkController;
use App\Http\Controllers\Api\SedeController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::prefix('auth')->group(function () {
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [UserController::class, 'logout']);
        Route::get('/me', [UserController::class, 'me']);
        Route::put('/me', [UserController::class, 'update']);
    });

    Route::prefix('network')->group(function () {
        Route::get('/summary', [NetworkController::class, 'summary']);
        Route::get('/links', [NetworkController::class, 'links']);
    });

    Route::apiResource('sedes', SedeController::class);
    Route::apiResource('enlaces', EnlaceController::class);

    Route::get('/alerts', [AlertController::class, 'index']);
});
