<?php

namespace App\Services;

use App\Models\ThresholdSetting;
use App\Models\PumpHouseThresholdSetting;
use App\Models\WaterLevelHistory;
use App\Models\PumpHouse;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Get active notifications based on current water levels
     */
    public function getActiveNotifications($userId = null)
    {
        $notifications = [];
        
        // Get latest water level for each pump house
        $pumpHousesQuery = PumpHouse::with(['waterLevelHistory' => function($query) {
            $query->latest('recorded_at')->limit(1);
        }]);
        
        // Filter berdasarkan akses user jika bukan admin
        if ($userId) {
            $user = \App\Models\User::find($userId);
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
     * Get threshold notifications (alias for getActiveNotifications for compatibility)
     */
    public function getThresholdNotifications($userId = null)
    {
        return $this->getActiveNotifications($userId);
    }
} 


