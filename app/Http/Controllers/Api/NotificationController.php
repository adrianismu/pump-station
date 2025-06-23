<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all active notifications
     */
    public function index()
    {
        $notifications = $this->notificationService->getActiveNotifications();
        $counts = $this->notificationService->getNotificationCounts();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $counts['total'],
            'counts' => $counts,
        ]);
    }

    /**
     * Get notification counts only
     */
    public function count()
    {
        $counts = $this->notificationService->getNotificationCounts();

        return response()->json([
            'unread_count' => $counts['total'],
            'counts' => $counts,
            'has_critical' => $this->notificationService->hasCriticalNotifications(),
        ]);
    }

    /**
     * Get recent threshold breaches
     */
    public function recentBreaches(Request $request)
    {
        $hours = $request->get('hours', 24);
        $breaches = $this->notificationService->getRecentBreaches($hours);

        return response()->json([
            'breaches' => $breaches,
            'count' => count($breaches),
        ]);
    }

    /**
     * Mark notification as read (placeholder for future implementation)
     */
    public function markAsRead($id)
    {
        // For threshold notifications, we don't need to mark as read
        // since they are based on current water levels
        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read (placeholder for future implementation)
     */
    public function markAllAsRead()
    {
        // For threshold notifications, we don't need to mark as read
        // since they are based on current water levels
        return response()->json(['success' => true]);
    }
}
