<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\TokenController;

use App\Http\Middleware\RateLimitLogin;

/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
*/

Route::post('/register', [
    AuthController::class,
    'register'
])->middleware([
    RateLimitLogin::class . ':register'
]);

Route::post('/login', [
    AuthController::class,
    'login'
])->middleware([
    RateLimitLogin::class . ':login'
]);

Route::post('/forgot-password', [
    PasswordResetController::class,
    'forgotPassword'
])->middleware([
    RateLimitLogin::class . ':forgot-password'
]);

Route::post('/reset-password', [
    PasswordResetController::class,
    'resetPassword'
])->middleware([
    RateLimitLogin::class . ':reset-password'
]);

/*
|--------------------------------------------------------------------------
| EMAIL VERIFICATION
|--------------------------------------------------------------------------
*/

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

    /*
    |--------------------------------------------------------------------------
    | EMAIL VERIFICATION
    |--------------------------------------------------------------------------
    */

    Route::get('/verification/status', [
        EmailVerificationController::class,
        'status'
    ]);

    Route::post('/verification/resend', [
        EmailVerificationController::class,
        'resend'
    ]);


    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [
        AuthController::class,
        'logout'
    ]);


    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard/statistics', [
        DashboardController::class,
        'statistics'
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

        /*
        |--------------------------------------------------------------------------
        | ACCOUNT DEACTIVATE / REACTIVATE
        |--------------------------------------------------------------------------
        */

        Route::post('/deactivate', [
            ProfileController::class,
            'deactivate'
        ]);

        Route::post('/reactivate', [
            ProfileController::class,
            'reactivate'
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | ACTIVITY LOG
    |--------------------------------------------------------------------------
    */

    Route::prefix('activities')->group(function () {

        Route::get('/', [
            ActivityLogController::class,
            'index'
        ]);

        Route::get('/{activityLog}', [
            ActivityLogController::class,
            'show'
        ]);
    });


    /*
    |--------------------------------------------------------------------------
    | DEVICE / TOKEN MANAGEMENT
    |--------------------------------------------------------------------------
    */

    Route::prefix('tokens')->group(function () {

        /*
        | List active devices
        */

        Route::get('/', [
            TokenController::class,
            'index'
        ]);

        /*
        | Revoke specific device
        */

        Route::delete('/{tokenId}', [
            TokenController::class,
            'revoke'
        ]);

        /*
        | Revoke all other devices
        */

        Route::delete('/others/all', [
            TokenController::class,
            'revokeOtherSessions'
        ]);

        /*
        | Revoke every session
        */

        Route::delete('/all', [
            TokenController::class,
            'revokeAllSessions'
        ]);
    });
});
