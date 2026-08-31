<?php

namespace App\Http\Controllers;

use App\Models\Auth;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Dashboard statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        $totalUsers = Auth::count();

        $activeUsers = Auth::where('status', 'active')
            ->count();

        $todayRegistrations = Auth::whereDate(
            'created_at',
            today()
        )->count();

        $monthlyRegistrations = Auth::whereYear(
            'created_at',
            now()->year
        )
            ->whereMonth(
                'created_at',
                now()->month
            )
            ->count();

        $verifiedUsers = Auth::whereNotNull(
            'email_verified_at'
        )->count();

        $deactivatedUsers = Auth::where(
            'status',
            'inactive'
        )->count();

        $bannedUsers = Auth::where(
            'status',
            'banned'
        )->count();

        $recentActivities = ActivityLog::where(
            'auth_id',
            $request->user('sanctum')->id
        )
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'statistics' => [
                    'total_users' => $totalUsers,
                    'active_users' => $activeUsers,
                    'verified_users' => $verifiedUsers,
                    'deactivated_users' => $deactivatedUsers,
                    'banned_users' => $bannedUsers,
                    'today_registrations' => $todayRegistrations,
                    'monthly_registrations' => $monthlyRegistrations,
                ],

                'account' => [
                    'name' => $request->user('sanctum')->name,
                    'email' => $request->user('sanctum')->email,
                    'status' => $request->user('sanctum')->status,
                    'last_login_at' => $request->user('sanctum')->last_login_at,
                    'last_login_ip' => $request->user('sanctum')->last_login_ip,
                ],

                'recent_activities' => $recentActivities,
            ],
        ]);
    }
}
