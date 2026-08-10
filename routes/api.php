<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\RateLimitLogin;

Route::post('/register', [AuthController::class, 'register'])
    ->middleware([RateLimitLogin::class . ':register']);

Route::post('/login', [AuthController::class, 'login'])
    ->middleware([RateLimitLogin::class . ':login']);

Route::get('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->prefix('profile')->group(function () {
    Route::get('/', [ProfileController::class, 'show']);
    Route::put('/', [ProfileController::class, 'update']);
    Route::patch('/password', [ProfileController::class, 'changePassword']);
});
