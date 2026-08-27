<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Send password reset link.
     */
    public function forgotPassword(
        ForgotPasswordRequest $request
    ): JsonResponse {
        $email = strtolower(trim($request->email));

        $user = Auth::where('email', $email)->first();

        /*
        |--------------------------------------------------------------------------
        | Do not reveal whether email exists.
        |--------------------------------------------------------------------------
        */

        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'If the email exists, a password reset link has been sent.',
            ]);
        }

        $status = Password::broker('auth')->sendResetLink([
            'email' => $email,
        ]);

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset link sent successfully.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __($status),
        ], 422);
    }

    /**
     * Reset password.
     */
    public function resetPassword(
        ResetPasswordRequest $request
    ): JsonResponse {
        $status = Password::broker('auth')->reset(
            [
                'email' => strtolower(trim($request->email)),
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'token' => $request->token,
            ],
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                /*
                |--------------------------------------------------------------------------
                | Revoke existing Sanctum tokens.
                |--------------------------------------------------------------------------
                */

                $user->tokens()->delete();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully. Please login again.',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __($status),
        ], 422);
    }
}