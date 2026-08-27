<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Middleware\RateLimitLogin;

/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register'])
    ->middleware([RateLimitLogin::class . ':register']);

Route::post('/login', [AuthController::class, 'login'])
    ->middleware([RateLimitLogin::class . ':login']);

Route::post('/forgot-password', [
    PasswordResetController::class,
    'forgotPassword'
])->middleware([RateLimitLogin::class . ':forgot-password']);

Route::post('/reset-password', [
    PasswordResetController::class,
    'resetPassword'
])->middleware([RateLimitLogin::class . ':reset-password']);

Route::get('/verify-email/{id}/{hash}', [
    EmailVerificationController::class,
    'verify'
])
    ->middleware('signed')
    ->name('verification.verify');


/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/verification/status', [
        EmailVerificationController::class,
        'status'
    ]);

    Route::post('/verification/resend', [
        EmailVerificationController::class,
        'resend'
    ]);

    Route::get('/logout', [
        AuthController::class,
        'logout'
    ]);

    /*
    |--------------------------------------------------------------------------
    | PROFILE
    |--------------------------------------------------------------------------
    */

    Route::prefix('profile')->group(function () {

        Route::get('/', [
            ProfileController::class,
            'show'
        ]);

        Route::put('/', [
            ProfileController::class,
            'update'
        ]);

        Route::patch('/password', [
            ProfileController::class,
            'changePassword'
        ]);
    });
});