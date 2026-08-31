<?php

namespace App\Http\Controllers;

use App\Models\Auth;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show profile.
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        return response()->json([
            'success' => true,
            'data' => $user,
        ]);
    }

    /**
     * Update profile.
     */
    public function update(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        $validated = $request->validate([
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'email' => [
                'sometimes',
                'email',
                'unique:auth,email,' . $user->id,
            ],
        ]);

        if (isset($validated['name'])) {
            $validated['name'] = strip_tags(
                $validated['name']
            );
        }

        if (isset($validated['email'])) {
            $validated['email'] = strtolower(
                trim($validated['email'])
            );
        }

        $user->update($validated);

        ActivityLog::create([
            'auth_id' => $user->id,
            'action' => 'profile_updated',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'fields' => array_keys($validated),
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user->fresh(),
        ]);
    }

    /**
     * Change password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        $validated = $request->validate([
            'current_password' => [
                'required',
                'current_password:sanctum',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->numbers(),
            ],
        ]);

        $user->update([
            'password' => Hash::make(
                $validated['password']
            ),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Revoke all other sessions
        |--------------------------------------------------------------------------
        */

        $currentToken = $user->currentAccessToken();

        if ($currentToken) {
            $user->tokens()
                ->where('id', '!=', $currentToken->id)
                ->delete();
        }

        ActivityLog::create([
            'auth_id' => $user->id,
            'action' => 'password_changed',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully. Other sessions have been logged out.',
        ]);
    }

    /**
     * Deactivate account.
     */
    public function deactivate(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        /*
        |--------------------------------------------------------------------------
        | Confirm current password
        |--------------------------------------------------------------------------
        */

        $request->validate([
            'current_password' => [
                'required',
                'current_password:sanctum',
            ],
        ]);

        $user->update([
            'status' => 'inactive',
        ]);

        ActivityLog::create([
            'auth_id' => $user->id,
            'action' => 'account_deactivated',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Delete all tokens except current token
        |--------------------------------------------------------------------------
        */

        $currentToken = $user->currentAccessToken();

        if ($currentToken) {
            $user->tokens()
                ->where('id', '!=', $currentToken->id)
                ->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Your account has been deactivated successfully.',
            'data' => [
                'status' => 'inactive',
            ],
        ]);
    }

    /**
     * Reactivate account.
     */
    public function reactivate(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        if ($user->status === 'banned') {
            return response()->json([
                'success' => false,
                'message' => 'A banned account cannot be reactivated.',
            ], 403);
        }

        if ($user->status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is already active.',
            ], 400);
        }

        $request->validate([
            'current_password' => [
                'required',
                'current_password:sanctum',
            ],
        ]);

        $user->update([
            'status' => 'active',
        ]);

        ActivityLog::create([
            'auth_id' => $user->id,
            'action' => 'account_reactivated',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your account has been reactivated successfully.',
            'data' => [
                'status' => 'active',
            ],
        ]);
    }
}
