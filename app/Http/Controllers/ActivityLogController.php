<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Get logged-in user's activity history.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');

        $perPage = min(
            max((int) $request->input('per_page', 10), 1),
            50
        );

        $logs = ActivityLog::where(
            'auth_id',
            $user->id
        )
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => [
                'activities' => $logs->items(),

                'pagination' => [
                    'current_page' => $logs->currentPage(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'total' => $logs->total(),
                ],
            ],
        ]);
    }

    /**
     * Get one activity.
     */
    public function show(
        Request $request,
        ActivityLog $activityLog
    ): JsonResponse {
        $user = $request->user('sanctum');

        /*
        |--------------------------------------------------------------------------
        | Security: user can only see own activity
        |--------------------------------------------------------------------------
        */

        if ($activityLog->auth_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Activity not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $activityLog,
        ]);
    }
}
