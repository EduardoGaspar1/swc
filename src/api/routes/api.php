<?php

use App\Http\Controllers\Api\AlertController;
use App\Http\Controllers\Api\NetworkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('network')->group(function () {
    Route::get('/summary', [NetworkController::class, 'summary']);
    Route::get('/links', [NetworkController::class, 'links']);
});

Route::get('/alerts', [AlertController::class, 'index']);
