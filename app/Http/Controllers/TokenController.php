<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    /**
     * Get all active Sanctum tokens.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        $currentToken = $user->currentAccessToken();

        $tokens = $user->tokens()
            ->latest('last_used_at')
            ->get()
            ->map(function ($token) use ($currentToken) {

                return [
                    'id' => $token->id,
                    'device_name' => $token->name,

                    'created_at' => $token->created_at,
                    'last_used_at' => $token->last_used_at,
                    'expires_at' => $token->expires_at,

                    'is_current' => $currentToken
                        && $currentToken->id === $token->id,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'tokens' => $tokens,
                'total' => $tokens->count(),
            ],
        ]);
    }

    /**
     * Revoke one token.
     */
    public function revoke(
        Request $request,
        int $tokenId
    ): JsonResponse {
        $user = $request->user('sanctum');

        $token = $user->tokens()
            ->where('id', $tokenId)
            ->first();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found.',
            ], 404);
        }

        $isCurrent = $user->currentAccessToken()
            && $user->currentAccessToken()->id === $token->id;

        $deviceName = $token->name;

        $token->delete();

        ActivityLog::create([
            'auth_id' => $user->id,
            'action' => 'token_revoked',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'token_id' => $tokenId,
                'device_name' => $deviceName,
                'current_token' => $isCurrent,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => $isCurrent
                ? 'Current session revoked successfully.'
                : 'Device session revoked successfully.',
        ]);
    }

    /**
     * Revoke all OTHER sessions.
     */
    public function revokeOtherSessions(
        Request $request
    ): JsonResponse {
        $user = $request->user('sanctum');

        $currentToken = $user->currentAccessToken();

        if (!$currentToken) {
            return response()->json([
                'success' => false,
                'message' => 'Current token not found.',
            ], 401);
        }

        $deletedCount = $user->tokens()
            ->where('id', '!=', $currentToken->id)
            ->delete();

        ActivityLog::create([
            'auth_id' => $user->id,
            'action' => 'other_sessions_revoked',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'sessions_revoked' => $deletedCount,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'All other sessions have been revoked.',
            'revoked_sessions' => $deletedCount,
        ]);
    }

    /**
     * Revoke ALL sessions.
     */
    public function revokeAllSessions(
        Request $request
    ): JsonResponse {
        $user = $request->user('sanctum');

        $count = $user->tokens()->count();

        $user->tokens()->delete();

        ActivityLog::create([
            'auth_id' => $user->id,
            'action' => 'all_sessions_revoked',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata' => [
                'sessions_revoked' => $count,
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'All sessions have been revoked. Please login again.',
            'revoked_sessions' => $count,
        ]);
    }
}
