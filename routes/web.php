<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Session;

Route::get('/me', function () {
    return Session::get('auth_user');
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES (CSRF DISABLED ONLY FOR THESE)
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register'])
    ->withoutMiddleware(VerifyCsrfToken::class);

Route::post('/login', [AuthController::class, 'login'])
    ->withoutMiddleware(VerifyCsrfToken::class);

Route::get('/logout', [AuthController::class, 'logout'])
    ->withoutMiddleware(VerifyCsrfToken::class);

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});
