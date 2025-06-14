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
     * Get active notifications based on current water levels and alerts
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
                'severity' => $this->mapSeverityToLegacy($alert['severity']),
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
        
        // Also get threshold-based notifications for real-time monitoring
        $thresholdNotifications = $this->getThresholdNotifications($userId);
        
        // Merge and deduplicate notifications
        $allNotifications = array_merge($notifications, $thresholdNotifications);
        
        // Remove duplicates based on pump house and keep most recent
        $uniqueNotifications = [];
        $processedPumpHouses = [];
        
        foreach ($allNotifications as $notification) {
            $pumpHouseName = $notification['pump_house'] ?? 'unknown';
            
            if (!in_array($pumpHouseName, $processedPumpHouses)) {
                $uniqueNotifications[] = $notification;
                $processedPumpHouses[] = $pumpHouseName;
            }
        }
        
        // Sort by severity and time
        usort($uniqueNotifications, function($a, $b) {
            $severityOrder = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
            $severityA = $severityOrder[$a['severity']] ?? 0;
            $severityB = $severityOrder[$b['severity']] ?? 0;
            
            if ($severityA === $severityB) {
                return strtotime($b['created_at'] ?? 'now') - strtotime($a['created_at'] ?? 'now');
            }
            
            return $severityB - $severityA;
        });
        
        return $uniqueNotifications;
    }

    /**
     * Get legacy threshold notifications (backward compatibility)
     */
    public function getThresholdNotifications($userId = null)
    {
        $notifications = [];
        
        // Get latest water level for each pump house
        $pumpHousesQuery = PumpHouse::with(['waterLevelHistory' => function($query) {
            $query->latest('recorded_at')->limit(1);
        }]);
        
        // Filter berdasarkan akses user jika bukan admin
        if ($userId) {
            $user = User::find($userId);
            if ($user && !$user->isAdmin()) {
                $accessibleIds = $user->getAccessiblePumpHouseIds();
                $pumpHousesQuery->whereIn('id', $accessibleIds);
            }
        }
        
        $pumpHouses = $pumpHousesQuery->get();

        foreach ($pumpHouses as $pumpHouse) {
            if ($pumpHouse->waterLevelHistory->isNotEmpty()) {
                $latestRecord = $pumpHouse->waterLevelHistory->first();
                $waterLevel = (float) $latestRecord->water_level;
                
                // Get exceeded threshold for this pump house
                $threshold = PumpHouseThresholdSetting::getExceededThresholdForPumpHouse($pumpHouse->id, $waterLevel);
                
                // Fallback to global threshold if no pump house specific threshold exists
                if (!$threshold) {
                    $threshold = ThresholdSetting::getExceededThreshold($waterLevel);
                }
                
                if ($threshold && $threshold->name !== 'normal') {
                    $notifications[] = [
                        'id' => 'threshold_' . $pumpHouse->id . '_' . $threshold->name,
                        'type' => 'threshold_exceeded',
                        'severity' => $threshold->severity,
                        'title' => $threshold->label . ' - ' . $pumpHouse->name,
                        'message' => "Ketinggian air mencapai {$waterLevel}m di {$pumpHouse->name}",
                        'description' => $threshold->description,
                        'color' => $threshold->color,
                        'pump_house' => $pumpHouse->name,
                        'location' => $pumpHouse->location,
                        'water_level' => $waterLevel,
                        'threshold_level' => $threshold->water_level,
                        'threshold_name' => $threshold->name,
                        'recorded_at' => $latestRecord->recorded_at,
                        'time_ago' => $latestRecord->recorded_at->diffForHumans(),
                        'actions' => [
                            [
                                'label' => 'Lihat Detail',
                                'route' => 'admin.water-level.show',
                                'params' => $latestRecord->id,
                                'type' => 'primary'
                            ],
                            [
                                'label' => 'Riwayat',
                                'route' => 'admin.water-level.history',
                                'params' => $pumpHouse->id,
                                'type' => 'secondary'
                            ]
                        ]
                    ];
                }
            }
        }

        // Sort by severity (critical first)
        usort($notifications, function($a, $b) {
            $severityOrder = ['critical' => 4, 'high' => 3, 'medium' => 2, 'low' => 1];
            return ($severityOrder[$b['severity']] ?? 0) - ($severityOrder[$a['severity']] ?? 0);
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
     * Check if there are any critical notifications
     */
    public function hasCriticalNotifications($userId = null)
    {
        $counts = $this->getNotificationCounts($userId);
        return $counts['critical'] > 0 || $counts['high'] > 0;
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
     * Map new severity levels to legacy severity levels
     */
    private function mapSeverityToLegacy($severity): string
    {
        return match($severity) {
            'Awas' => 'critical',
            'Siaga' => 'high',
            default => 'medium'
        };
    }

    /**
     * Get color for severity level
     */
    private function getSeverityColor($severity): string
    {
        return match($severity) {
            'Awas' => '#dc2626',
            'Siaga' => '#ea580c',
            default => '#0ea5e9'
        };
    }
} 


