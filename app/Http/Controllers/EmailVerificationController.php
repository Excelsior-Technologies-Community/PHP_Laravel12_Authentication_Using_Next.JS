<?php

namespace App\Http\Controllers;

use App\Models\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    /**
     * Verify email address.
     */
    public function verify(
        Request $request,
        int $id,
        string $hash
    ) {
        $user = Auth::find($id);

        if (!$user) {
            return redirect(
                config('app.frontend_url') . '/verify-email?status=invalid'
            );
        }

        if (!hash_equals(
            sha1($user->getEmailForVerification()),
            $hash
        )) {
            return redirect(
                config('app.frontend_url') . '/verify-email?status=invalid'
            );
        }

        if ($user->hasVerifiedEmail()) {
            return redirect(
                config('app.frontend_url') . '/verify-email?status=already'
            );
        }

        $user->markEmailAsVerified();

        return redirect(
            config('app.frontend_url') . '/verify-email?status=success'
        );
    }

    /**
     * Resend verification email.
     */
    public function resend(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email address is already verified.',
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent successfully.',
        ]);
    }

    /**
     * Return verification status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        return response()->json([
            'success' => true,
            'data' => [
                'email_verified' => $user->hasVerifiedEmail(),
                'email_verified_at' => $user->email_verified_at,
            ],
        ]);
    }
}