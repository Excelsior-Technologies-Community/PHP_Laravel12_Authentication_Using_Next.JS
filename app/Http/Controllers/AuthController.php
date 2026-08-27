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
        ]);

        /*
        |--------------------------------------------------------------------------
        | Send email verification notification
        |--------------------------------------------------------------------------
        */

        $user->sendEmailVerificationNotification();

        $token = $user
            ->createToken('auth_token')
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

        $user = Auth::where(
            'email',
            strtolower(trim($validated['email']))
        )->first();

        if (
            !$user ||
            !Hash::check(
                $validated['password'],
                $user->password
            )
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        if ($user->status === 'banned') {
            return response()->json([
                'success' => false,
                'message' =>
                    'Your account has been banned. Please contact support.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | Require email verification
        |--------------------------------------------------------------------------
        */

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Please verify your email address before logging in.',
                'email_verification_required' => true,
            ], 403);
        }

        $token = $user
            ->createToken('auth_token')
            ->plainTextToken;

        ActivityLog::create([
            'auth_id' => $user->id,
            'action' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => $user,
                'token' => $token,
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

            $user->tokens()->delete();
        }

        Session::forget('auth_user');

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
        ]);
    }
}