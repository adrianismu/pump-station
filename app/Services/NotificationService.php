<?php

namespace App\Services;

use App\Models\ThresholdSetting;
use App\Models\PumpHouseThresholdSetting;
use App\Models\WaterLevelHistory;
use App\Models\PumpHouse;
use App\Models\Alert;
use App\Models\User;
use App\Notifications\WeatherAlertNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Distribute alert notifications to appropriate users based on role and context
     */
    public function distributeAlertNotifications(Alert $alert)
    {
        $recipients = [];

        // Always send to all admins
        $admins = User::role('admin')->get();
        foreach ($admins as $admin) {
            $recipients[] = [
                'user_id' => $admin->id,
                'message' => $alert->internal_message,
                'type' => 'internal',
                'role' => 'admin'
            ];
        }

        // Send to relevant petugas based on alert type
        if ($alert->type === 'water_level') {
            // For water level alerts, send only to petugas with access to this pump house
            $relevantPetugas = User::role('petugas')
                ->whereHas('pumpHouses', function($query) use ($alert) {
                    $query->where('pump_house_id', $alert->pump_house_id)
                          ->where('is_active', true);
                })
                ->get();

            foreach ($relevantPetugas as $petugas) {
                $recipients[] = [
                    'user_id' => $petugas->id,
                    'message' => $alert->internal_message,
                    'type' => 'internal',
                    'role' => 'petugas'
                ];
            }
        } elseif ($alert->type === 'weather_forecast') {
            // For weather alerts, send to all petugas
            $allPetugas = User::role('petugas')->get();
            foreach ($allPetugas as $petugas) {
                $recipients[] = [
                    'user_id' => $petugas->id,
                    'message' => $alert->internal_message,
                    'type' => 'internal',
                    'role' => 'petugas'
                ];
            }
        }

        // Store notifications in database (you can implement this based on your notification table structure)
        $this->storeNotifications($alert, $recipients);

        return $recipients;
    }

    /**
     * Store notifications in database using Laravel notification system
     */
    private function storeNotifications(Alert $alert, array $recipients)
    {
        try {
            foreach ($recipients as $recipient) {
                $user = User::find($recipient['user_id']);
                if ($user) {
                    $user->notify(new WeatherAlertNotification($alert));
                    Log::info('Notification sent successfully', [
                        'user_id' => $user->id,
                        'user_email' => $user->email,
                        'alert_id' => $alert->id,
                        'alert_type' => $alert->type,
                        'environment' => app()->environment()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to send notifications', [
                'alert_id' => $alert->id,
                'error' => $e->getMessage(),
                'recipients_count' => count($recipients),
                'environment' => app()->environment()
            ]);
        }
    }

    /**
     * Get active notifications based on alerts only (no duplicate threshold checking)
     */
    public function getActiveNotifications($userId = null)
    {
        $notifications = [];
        
        // Get contextual alerts (alerts from last 24 hours)
        $recentAlerts = $this->getContextualizedAlerts($userId, 24);
        
        // Convert alerts to notification format
        foreach ($recentAlerts as $alert) {
            $notifications[] = [
                'id' => 'alert_' . $alert['id'],
                'type' => $alert['type'],
                'severity' => $alert['severity'], // Use consistent severity system
                'title' => $alert['title'],
                'message' => $alert['description'],
                'description' => $alert['internal_message'],
                'color' => $this->getSeverityColor($alert['severity']),
                'pump_house' => $alert['pump_house'],
                'location' => $alert['location'],
                'created_at' => $alert['created_at'],
                'time_ago' => $alert['time_ago'],
                'actions' => $alert['actions'],
            ];
        }
        
        // Sort by severity and time
        usort($notifications, function($a, $b) {
            $severityOrder = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
            $severityA = $severityOrder[$a['severity']] ?? 0;
            $severityB = $severityOrder[$b['severity']] ?? 0;
            
            if ($severityA === $severityB) {
                return strtotime($b['created_at'] ?? 'now') - strtotime($a['created_at'] ?? 'now');
            }
            
            return $severityB - $severityA;
        });
        
        return $notifications;
    }



    /**
     * Get contextualized alerts for user based on role and access
     */
    public function getContextualizedAlerts($userId = null, $hoursBack = null)
    {
        $alertsQuery = Alert::with('pump_house')
            ->where('is_active', true)
            ->orderBy('created_at', 'desc');

        // Filter by time if specified (default: last 24 hours for notifications)
        if ($hoursBack !== null) {
            $alertsQuery->where('created_at', '>', now()->subHours($hoursBack));
        }

        // Filter berdasarkan akses user jika bukan admin
        if ($userId) {
            $user = User::find($userId);
            if ($user && !$user->isAdmin()) {
                $accessibleIds = $user->getAccessiblePumpHouseIds();
                $alertsQuery->whereIn('pump_house_id', $accessibleIds);
            }
        }

        $alerts = $alertsQuery->get();

        return $alerts->map(function ($alert) {
            return [
                'id' => $alert->id,
                'type' => $alert->type,
                'severity' => $alert->severity,
                'title' => $alert->title,
                'description' => $alert->description,
                'internal_message' => $alert->internal_message,
                'public_message' => $alert->public_message,
                'pump_house' => $alert->pump_house->name,
                'location' => $alert->pump_house->address,
                'created_at' => $alert->created_at,
                'time_ago' => $alert->created_at->diffForHumans(),
                'actions' => $this->getAlertActions($alert),
            ];
        });
    }

    /**
     * Get appropriate actions for an alert based on its type
     */
    private function getAlertActions(Alert $alert): array
    {
        $actions = [];

        if ($alert->type === 'water_level') {
            $actions[] = [
                'label' => 'Lihat Detail',
                'route' => 'admin.database.show',
                'params' => $alert->pump_house_id,
                'type' => 'primary'
            ];
            $actions[] = [
                'label' => 'Riwayat Level Air',
                'route' => 'admin.water-level.history',
                'params' => $alert->pump_house_id,
                'type' => 'secondary'
            ];
        } elseif ($alert->type === 'weather_forecast') {
            $actions[] = [
                'label' => 'Lihat Rumah Pompa',
                'route' => 'admin.database.show',
                'params' => $alert->pump_house_id,
                'type' => 'primary'
            ];
            $actions[] = [
                'label' => 'Pantau Cuaca',
                'route' => 'admin.map',
                'params' => null,
                'type' => 'secondary'
            ];
        }

        return $actions;
    }

    /**
     * Get notification count by severity
     */
    public function getNotificationCounts($userId = null)
    {
        $notifications = $this->getActiveNotifications($userId);
        
        $counts = [
            'total' => count($notifications),
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
        ];

        foreach ($notifications as $notification) {
            $severity = $notification['severity'];
            if (isset($counts[$severity])) {
                $counts[$severity]++;
            }
        }

        return $counts;
    }

    /**
     * Check if there are any important notifications
     */
    public function hasCriticalNotifications($userId = null)
    {
        $counts = $this->getNotificationCounts($userId);
        return $counts['critical'] > 0 || $counts['high'] > 0 || $counts['medium'] > 0;
    }

    /**
     * Get recent threshold breaches (last 24 hours)
     */
    public function getRecentBreaches($hours = 24)
    {
        $since = Carbon::now()->subHours($hours);
        
        $recentRecords = WaterLevelHistory::with('pumpHouse')
            ->where('recorded_at', '>=', $since)
            ->orderBy('recorded_at', 'desc')
            ->get();

        $breaches = [];
        
        foreach ($recentRecords as $record) {
            $waterLevel = (float) $record->water_level;
            
            // Get exceeded threshold for this pump house
            $threshold = PumpHouseThresholdSetting::getExceededThresholdForPumpHouse($record->pump_house_id, $waterLevel);
            
            // Fallback to global threshold if no pump house specific threshold exists
            if (!$threshold) {
                $threshold = ThresholdSetting::getExceededThreshold($waterLevel);
            }
            
            if ($threshold && $threshold->name !== 'normal') {
                $breaches[] = [
                    'pump_house' => $record->pumpHouse->name,
                    'location' => $record->pumpHouse->location,
                    'water_level' => $waterLevel,
                    'threshold' => $threshold,
                    'recorded_at' => $record->recorded_at,
                    'time_ago' => $record->recorded_at->diffForHumans(),
                ];
            }
        }

        return $breaches;
    }



    /**
     * Get severity color for display
     */
    public function getSeverityColor(string $severity): string
    {
        return match($severity) {
            'critical' => '#dc2626', // red-600
            'high' => '#ea580c',      // orange-600  
            'medium' => '#d97706',    // amber-600
            'low' => '#059669',       // emerald-600
            default => '#0ea5e9'      // sky-500
        };
    }

    /**
     * Get severity label for display
     */
    public function getSeverityLabel(string $severity): string
    {
        return match($severity) {
            'critical' => 'Kritis',
            'high' => 'Tinggi', 
            'medium' => 'Sedang',
            'low' => 'Rendah',
            default => 'Normal'
        };
    }
} 


