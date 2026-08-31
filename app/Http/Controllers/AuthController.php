<?php

namespace App\Http\Controllers;

use App\Models\Auth;
use App\Models\ActivityLog;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Security Configuration
    |--------------------------------------------------------------------------
    */

    private const MAX_LOGIN_ATTEMPTS = 5;

    private const LOCKOUT_MINUTES = 15;

    /**
     * REGISTER
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = Auth::create([
            'name' => strip_tags($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'password' => Hash::make($validated['password']),
            'status' => 'active',
            'email_verified_at' => null,

            'failed_login_attempts' => 0,
            'locked_until' => null,

            'last_login_at' => null,
            'last_login_ip' => null,
            'last_login_user_agent' => null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Send email verification
        |--------------------------------------------------------------------------
        */

        $user->sendEmailVerificationNotification();

        $token = $user
            ->createToken('Web Browser')
            ->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please verify your email.',
            'data' => [
                'user' => $user,
                'token' => $token,
                'email_verified' => false,
            ],
        ], 201);
    }

    /**
     * LOGIN
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $email = strtolower(trim($validated['email']));

        $user = Auth::where('email', $email)->first();

        /*
        |--------------------------------------------------------------------------
        | Invalid User
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Check Account Lock
        |--------------------------------------------------------------------------
        */

        if ($user->isLocked()) {
            $remainingMinutes = max(
                1,
                now()->diffInMinutes($user->locked_until, false)
            );

            return response()->json([
                'success' => false,
                'message' => "Account temporarily locked. Please try again in {$remainingMinutes} minute(s).",
                'account_locked' => true,
                'locked_until' => $user->locked_until,
                'remaining_minutes' => $remainingMinutes,
            ], 423);
        }

        /*
        |--------------------------------------------------------------------------
        | Check Banned Account
        |--------------------------------------------------------------------------
        */

        if ($user->status === 'banned') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been banned. Please contact support.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Check Deactivated Account
        |--------------------------------------------------------------------------
        */

        if ($user->status === 'inactive') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is currently deactivated. Please reactivate your account.',
                'account_deactivated' => true,
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Check Password
        |--------------------------------------------------------------------------
        */

        if (!Hash::check($validated['password'], $user->password)) {

            $user->increment('failed_login_attempts');

            $user->refresh();

            /*
            |--------------------------------------------------------------------------
            | Lock after 5 failed attempts
            |--------------------------------------------------------------------------
            */

            if ($user->failed_login_attempts >= self::MAX_LOGIN_ATTEMPTS) {

                $user->update([
                    'locked_until' => now()->addMinutes(
                        self::LOCKOUT_MINUTES
                    ),
                ]);

                ActivityLog::create([
                    'auth_id' => $user->id,
                    'action' => 'account_locked',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'metadata' => [
                        'failed_attempts' => $user->failed_login_attempts,
                        'locked_minutes' => self::LOCKOUT_MINUTES,
                    ],
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Too many failed login attempts. Your account has been locked for 15 minutes.',
                    'account_locked' => true,
                    'locked_until' => $user->locked_until,
                ], 423);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
                'remaining_attempts' => self::MAX_LOGIN_ATTEMPTS
                    - $user->failed_login_attempts,
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | Require Email Verification
        |--------------------------------------------------------------------------
        */

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email address before logging in.',
                'email_verification_required' => true,
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Successful Login
        |--------------------------------------------------------------------------
        */

        $deviceName = $request->input(
            'device_name',
            'Web Browser'
        );

        /*
        |--------------------------------------------------------------------------
        | Reset Login Attempts
        |--------------------------------------------------------------------------
        */

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,

            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
            'last_login_user_agent' => $request->userAgent(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create Sanctum Token
        |--------------------------------------------------------------------------
        */

        $token = $user
            ->createToken($deviceName)
            ->plainTextToken;

        /*
        |--------------------------------------------------------------------------
        | Activity Log
        |--------------------------------------------------------------------------
        */

        ActivityLog::create([
            'auth_id' => $user->id,
            'action' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'device_name' => $deviceName,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,

                'last_login' => [
                    'at' => $user->last_login_at,
                    'ip' => $user->last_login_ip,
                    'user_agent' => $user->last_login_user_agent,
                ],
            ],
        ]);
    }

    /**
     * DASHBOARD
     */
    public function dashboard()
    {
        if (!Session::has('auth_user')) {
            return redirect('/login');
        }

        return view('dashboard');
    }

    /**
     * LOGOUT
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        if ($user) {

            ActivityLog::create([
                'auth_id' => $user->id,
                'action' => 'logout',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'metadata' => null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Revoke current token only
            |--------------------------------------------------------------------------
            */

            $currentToken = $user->currentAccessToken();

            if ($currentToken) {
                $currentToken->delete();
            }
        }

        Session::forget('auth_user');

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}
