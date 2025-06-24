<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use App\Models\AlertAction;
use App\Models\PumpHouse;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Filter alerts berdasarkan akses user
        $alertsQuery = Alert::with('pump_house');
        
        if (!$user->isAdmin()) {
            $accessibleIds = $user->getAccessiblePumpHouseIds();
            $alertsQuery->whereIn('pump_house_id', $accessibleIds);
        }
        
        $alerts = $alertsQuery->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($alert) {
                return [
                    'id' => $alert->id,
                    'title' => $alert->title,
                    'description' => $alert->description,
                    'severity' => $alert->severity,
                    'created_at' => $alert->created_at,
                    'updated_at' => $alert->updated_at,
                    'timestamp' => $alert->created_at, // Raw timestamp untuk frontend processing
                    'location' => $alert->pump_house->name,
                    'waterLevel' => $alert->water_level ? $alert->water_level . ' cm' : 'N/A',
                    'pumpStatus' => $alert->pump_status ?: 'N/A',
                    'rainfall' => $alert->rainfall ? $alert->rainfall . ' mm' : 'N/A',
                    'pump_house' => [
                        'id' => $alert->pump_house->id,
                        'name' => $alert->pump_house->name
                    ]
                ];
            });
            
        // Filter pump houses untuk dropdown
        $pumpHousesQuery = PumpHouse::select('id', 'name');
        
        if (!$user->isAdmin()) {
            $accessibleIds = $user->getAccessiblePumpHouseIds();
            $pumpHousesQuery->whereIn('id', $accessibleIds);
        }
        
        $pumpHouses = $pumpHousesQuery->get();
        
        return Inertia::render('Admin/Notifications/Index', [
            'alerts' => $alerts,
            'pumpHouses' => $pumpHouses,
            'userRole' => $user->role,
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'pump_house_id' => 'required|exists:pump_houses,id',
            'severity' => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
            'recipients' => 'nullable|array',
        ]);
        
        $alert = Alert::create($validated);
        
        // Here you would handle notification sending logic
        // For example, sending emails, SMS, or push notifications
        
        return redirect()->route('admin.notifications');
    }

    public function show(Alert $alert)
    {
        $user = auth()->user();
        
        // Cek akses untuk petugas
        if (!$user->isAdmin() && !$user->hasAccessToPumpHouse($alert->pump_house_id)) {
            abort(403, 'Anda tidak memiliki akses ke notifikasi ini.');
        }
        
        // Load the alert with its relationships
        $alert->load(['pump_house', 'actions.user']);
        
        // Get water level history for the pump house (last 3 months)
        $waterLevelHistory = [];
        $thresholds = [];
        $globalThresholds = [];
        
        if ($alert->pump_house_id) {
            // Get water level history
            $waterLevelHistory = \App\Models\WaterLevelHistory::where('pump_house_id', $alert->pump_house_id)
                ->where('recorded_at', '>=', now()->subMonths(3))
                ->orderBy('recorded_at', 'asc')
                ->get()
                ->map(function ($record) {
                    return [
                        'id' => $record->id,
                        'water_level' => (float) $record->water_level,
                        'recorded_at' => $record->recorded_at->format('Y-m-d H:i:s'),
                        'timestamp' => $record->recorded_at->timestamp * 1000, // For chart
                    ];
                });
            
            // Get pump house specific thresholds
            $thresholds = \App\Models\PumpHouseThresholdSetting::where('pump_house_id', $alert->pump_house_id)
                ->where('is_active', true)
                ->orderBy('water_level', 'asc')
                ->get()
                ->map(function ($setting) {
                    return [
                        'id' => $setting->id,
                        'name' => $setting->name,
                        'label' => $setting->label,
                        'water_level' => (float) $setting->water_level,
                        'color' => $setting->color,
                        'severity' => $setting->severity,
                        'description' => $setting->description,
                    ];
                });
        }
        
        // Get global thresholds as fallback
        $globalThresholds = \App\Models\ThresholdSetting::orderBy('water_level', 'asc')
            ->get()
            ->map(function ($threshold) {
                return [
                    'id' => $threshold->id,
                    'name' => $threshold->name,
                    'label' => $threshold->label,
                    'water_level' => (float) $threshold->water_level,
                    'color' => $threshold->color,
                    'severity' => $threshold->severity,
                    'description' => $threshold->description,
                ];
            });
        
        // Get related alerts (same pump house or similar severity)
        $relatedAlertsQuery = Alert::where('id', '!=', $alert->id)
            ->where(function ($query) use ($alert) {
                $query->where('pump_house_id', $alert->pump_house_id)
                      ->orWhere('severity', $alert->severity);
            })
            ->with('pump_house');
            
        // Filter related alerts untuk petugas
        if (!$user->isAdmin()) {
            $accessibleIds = $user->getAccessiblePumpHouseIds();
            $relatedAlertsQuery->whereIn('pump_house_id', $accessibleIds);
        }
        
        $relatedAlerts = $relatedAlertsQuery->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($relatedAlert) {
                return [
                    'id' => $relatedAlert->id,
                    'title' => $relatedAlert->title,
                    'severity' => $relatedAlert->severity,
                    'timestamp' => $relatedAlert->created_at->format('d M Y, H:i'),
                    'location' => $relatedAlert->pump_house->name,
                ];
            });
        
        // Format the alert data for the frontend
        $alertData = [
            'id' => $alert->id,
            'title' => $alert->title,
            'description' => $alert->description,
            'severity' => $alert->severity,
            'created_at' => $alert->created_at,
            'updated_at' => $alert->updated_at,
            'water_level' => $alert->water_level,
            'pump_status' => $alert->pump_status,
            'rainfall' => $alert->rainfall,
            'recipients' => $alert->recipients,
            'pump_house' => [
                'id' => $alert->pump_house->id,
                'name' => $alert->pump_house->name,
                'address' => $alert->pump_house->address,
                'location' => $alert->pump_house->location,
                'lat' => $alert->pump_house->latitude,
                'lng' => $alert->pump_house->longitude,
            ],
            'actions' => $alert->actions->map(function ($action) {
                return [
                    'id' => $action->id,
                    'description' => $action->description,
                    'status' => $action->status,
                    'created_at' => $action->created_at->format('d M Y, H:i'),
                    'user' => $action->user ? [
                        'name' => $action->user->name,
                        'role' => $action->user->role,
                    ] : null,
                ];
            }),
        ];
        
        return Inertia::render('Admin/Notifications/Show', [
            'alert' => $alertData,
            'waterLevelHistory' => $waterLevelHistory,
            'thresholds' => $thresholds,
            'globalThresholds' => $globalThresholds,
            'relatedAlerts' => $relatedAlerts,
            'userRole' => $user->role,
        ]);
    }
    
    public function addAction(Request $request, Alert $alert)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'status' => 'required|string',
        ]);
        
        $action = new AlertAction([
            'description' => $validated['description'],
            'status' => $validated['status'],
            'user_id' => auth()->id(),
        ]);
        
        $alert->actions()->save($action);
        
        return redirect()->back();
    }
    
    public function update(Request $request, Alert $alert)
    {
        $validated = $request->validate([
            'severity' => 'required|in:low,medium,high,critical',
            'status' => 'required|string',
        ]);
        
        $alert->update($validated);
        
        return redirect()->back();
    }
    
    // API methods for AJAX calls
    public function apiIndex()
    {
        $user = auth()->user();
        $notificationService = new NotificationService();
        $notifications = $notificationService->getActiveNotifications($user->id);
        
        return response()->json([
            'notifications' => $notifications,
            'unread_count' => count($notifications)
        ]);
    }
    
    public function apiCount()
    {
        try {
            $user = auth()->user();
            $notificationService = new NotificationService();
            
            // Get notifications from last 24 hours only
            $notifications = $notificationService->getActiveNotifications($user->id);
            
            // Filter by severity - count important notifications (medium, high, critical)
            $importantNotifications = array_filter($notifications, function($notification) {
                return in_array($notification['severity'], ['medium', 'high', 'critical']);
            });
            
            return response()->json([
                'unread_count' => count($importantNotifications),
                'total_notifications' => count($notifications),
                'critical_count' => count(array_filter($notifications, fn($n) => $n['severity'] === 'critical')),
                'high_count' => count(array_filter($notifications, fn($n) => $n['severity'] === 'high')),
                'medium_count' => count(array_filter($notifications, fn($n) => $n['severity'] === 'medium')),
                'debug' => 'Important notifications (medium + high + critical)',
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in apiCount: ' . $e->getMessage());
            
            return response()->json([
                'unread_count' => 0,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    public function markAsRead(Alert $alert)
    {
        // For now, just return success since we don't have read status in alerts table
        return response()->json(['success' => true]);
    }
    
    public function markAllAsRead()
    {
        // For now, just return success since we don't have read status in alerts table
        return response()->json(['success' => true]);
    }
}