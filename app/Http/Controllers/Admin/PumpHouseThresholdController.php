<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PumpHouse;
use App\Models\PumpHouseThresholdSetting;
use App\Models\ThresholdSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PumpHouseThresholdController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            // Admin bisa akses semua pump house
            $pumpHouses = PumpHouse::with([
                'threshold_settings' => function($query) {
                    $query->orderBy('water_level', 'asc');
                },
                'waterLevelHistory' => function($query) {
                    $query->orderBy('recorded_at', 'desc')->limit(1);
                }
            ])->get();
            
            // Add access info for admin (always full access)
            $pumpHouses = $pumpHouses->map(function($pumpHouse) {
                $pumpHouse->user_access = [
                    'level' => 'admin',
                    'can_read' => true,
                    'can_write' => true,
                    'can_admin' => true,
                ];
                
                // Add current water level data
                $latestWaterLevel = $pumpHouse->waterLevelHistory->first();
                $pumpHouse->current_water_level = $latestWaterLevel?->water_level ?? null;
                $pumpHouse->last_recorded_at = $latestWaterLevel?->recorded_at ?? null;
                
                return $pumpHouse;
            });
        } else {
            // Petugas hanya bisa akses pump house yang ditugaskan
            $pumpHouses = PumpHouse::with([
                'threshold_settings' => function($query) {
                    $query->orderBy('water_level', 'asc');
                },
                'waterLevelHistory' => function($query) {
                    $query->orderBy('recorded_at', 'desc')->limit(1);
                }
            ])
            ->whereHas('users', function($query) use ($user) {
                $query->where('users.id', $user->id)
                      ->where('user_pump_house.is_active', true)
                      ->where(function($q) {
                          $q->whereNull('user_pump_house.expires_at')
                            ->orWhere('user_pump_house.expires_at', '>', now());
                      });
            })
            ->get();
            
            // Add detailed access info for each pump house
            $pumpHouses = $pumpHouses->map(function($pumpHouse) use ($user) {
                $userAccess = $user->allPumpHouses()
                    ->where('pump_houses.id', $pumpHouse->id)
                    ->first();
                
                $accessLevel = $userAccess ? $userAccess->pivot->access_level : 'read';
                
                $pumpHouse->user_access = [
                    'level' => $accessLevel,
                    'can_read' => true, // Minimal access
                    'can_write' => in_array($accessLevel, ['write', 'admin']),
                    'can_admin' => $accessLevel === 'admin',
                ];
                
                // Add current water level data
                $latestWaterLevel = $pumpHouse->waterLevelHistory->first();
                $pumpHouse->current_water_level = $latestWaterLevel?->water_level ?? null;
                $pumpHouse->last_recorded_at = $latestWaterLevel?->recorded_at ?? null;
                
                return $pumpHouse;
            });
        }

        return Inertia::render('Admin/PumpHouseThreshold/Index', [
            'pumpHouses' => $pumpHouses,
            'userRole' => $user->role,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    public function show($pumpHouseId)
    {
        $user = auth()->user();
        
        // Authorization check
        if (!$user->isAdmin() && !$user->hasAccessToPumpHouse($pumpHouseId, 'read')) {
            abort(403, 'Anda tidak memiliki akses ke rumah pompa ini.');
        }
        
        $pumpHouse = PumpHouse::with(['threshold_settings' => function($query) {
            $query->orderBy('water_level', 'asc');
        }, 'waterLevelHistory' => function($query) {
            $query->orderBy('recorded_at', 'desc')->limit(10);
        }])->findOrFail($pumpHouseId);

        // Get current water level (latest record)
        $currentWaterLevel = $pumpHouse->waterLevelHistory->first()?->water_level ?? 0;

        // Get recent history (last 90 days for chart, limit 100)
        $recentHistory = $pumpHouse->waterLevelHistory()
            ->where('recorded_at', '>=', now()->subDays(90))
            ->orderBy('recorded_at', 'desc')
            ->limit(100)
            ->get();

        // Get total history count
        $totalHistoryCount = $pumpHouse->waterLevelHistory()->count();

        // Get global thresholds as fallback
        $globalThresholds = \App\Models\ThresholdSetting::where('is_active', true)
            ->orderBy('water_level', 'asc')
            ->get();

        return Inertia::render('Admin/PumpHouseThreshold/Show', [
            'pumpHouse' => $pumpHouse,
            'thresholds' => $pumpHouse->threshold_settings,
            'currentWaterLevel' => $currentWaterLevel,
            'recentHistory' => $recentHistory,
            'totalHistoryCount' => $totalHistoryCount,
            'globalThresholds' => $globalThresholds,
            'userRole' => $user->role,
            'isAdmin' => $user->isAdmin(),
            'canWrite' => $user->isAdmin() || $user->hasAccessToPumpHouse($pumpHouseId, 'write'),
        ]);
    }

    public function edit($pumpHouseId)
    {
        $user = auth()->user();
        
        // Authorization check - perlu akses write
        if (!$user->isAdmin() && !$user->hasAccessToPumpHouse($pumpHouseId, 'write')) {
            abort(403, 'Anda tidak memiliki akses write ke rumah pompa ini.');
        }
        
        $pumpHouse = PumpHouse::with([
            'threshold_settings' => function($query) {
                $query->orderBy('water_level', 'asc');
            },
            'waterLevelHistory' => function($query) {
                $query->orderBy('recorded_at', 'desc')->limit(1);
            }
        ])->findOrFail($pumpHouseId);

        // Add current water level data
        $latestWaterLevel = $pumpHouse->waterLevelHistory->first();
        $pumpHouse->current_water_level = $latestWaterLevel?->water_level ?? null;
        $pumpHouse->last_recorded_at = $latestWaterLevel?->recorded_at ?? null;

        // If no thresholds exist for this pump house, copy from default
        if ($pumpHouse->threshold_settings->isEmpty()) {
            PumpHouseThresholdSetting::copyDefaultThresholds($pumpHouseId);
            $pumpHouse->load('threshold_settings');
        }

        return Inertia::render('Admin/PumpHouseThreshold/Edit', [
            'pumpHouse' => $pumpHouse,
            'userRole' => $user->role,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    public function update(Request $request, $pumpHouseId)
    {
        $user = auth()->user();
        
        // Authorization check - perlu akses write
        if (!$user->isAdmin() && !$user->hasAccessToPumpHouse($pumpHouseId, 'write')) {
            abort(403, 'Anda tidak memiliki akses write ke rumah pompa ini.');
        }
        
        $pumpHouse = PumpHouse::findOrFail($pumpHouseId);
        
        $request->validate([
            'thresholds' => 'required|array',
            'thresholds.*.id' => 'nullable|exists:pump_house_threshold_settings,id',
            'thresholds.*.name' => 'required|string',
            'thresholds.*.label' => 'required|string',
            'thresholds.*.water_level' => 'required|numeric|min:0|max:10',
            'thresholds.*.color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'thresholds.*.severity' => 'required|in:low,medium,high,critical',
            'thresholds.*.is_active' => 'boolean',
            'thresholds.*.description' => 'nullable|string',
        ]);

        foreach ($request->thresholds as $thresholdData) {
            if (isset($thresholdData['id'])) {
                // Update existing threshold
                $threshold = PumpHouseThresholdSetting::findOrFail($thresholdData['id']);
                $threshold->update([
                    'label' => $thresholdData['label'],
                    'water_level' => $thresholdData['water_level'],
                    'color' => $thresholdData['color'],
                    'severity' => $thresholdData['severity'],
                    'is_active' => $thresholdData['is_active'] ?? true,
                    'description' => $thresholdData['description'],
                ]);
            } else {
                // Create new threshold
                PumpHouseThresholdSetting::create([
                    'pump_house_id' => $pumpHouseId,
                    'name' => $thresholdData['name'],
                    'label' => $thresholdData['label'],
                    'water_level' => $thresholdData['water_level'],
                    'color' => $thresholdData['color'],
                    'severity' => $thresholdData['severity'],
                    'is_active' => $thresholdData['is_active'] ?? true,
                    'description' => $thresholdData['description'],
                ]);
            }
        }

        return redirect()->route('admin.pump-house-thresholds.show', $pumpHouseId)
            ->with('success', 'Pengaturan threshold berhasil diperbarui');
    }

    public function copyFromDefault($pumpHouseId)
    {
        $user = auth()->user();
        
        // Authorization check - perlu akses write
        if (!$user->isAdmin() && !$user->hasAccessToPumpHouse($pumpHouseId, 'write')) {
            abort(403, 'Anda tidak memiliki akses write ke rumah pompa ini.');
        }
        
        $pumpHouse = PumpHouse::findOrFail($pumpHouseId);
        
        PumpHouseThresholdSetting::copyDefaultThresholds($pumpHouseId);

        return redirect()->route('admin.pump-house-thresholds.edit', $pumpHouseId)
            ->with('success', 'Threshold default berhasil disalin');
    }

    public function resetToDefault($pumpHouseId)
    {
        $user = auth()->user();
        
        // Authorization check - perlu akses write
        if (!$user->isAdmin() && !$user->hasAccessToPumpHouse($pumpHouseId, 'write')) {
            abort(403, 'Anda tidak memiliki akses write ke rumah pompa ini.');
        }
        
        $pumpHouse = PumpHouse::findOrFail($pumpHouseId);
        
        // Delete existing thresholds
        PumpHouseThresholdSetting::where('pump_house_id', $pumpHouseId)->delete();
        
        // Copy default thresholds
        PumpHouseThresholdSetting::copyDefaultThresholds($pumpHouseId);

        return redirect()->route('admin.pump-house-thresholds.edit', $pumpHouseId)
            ->with('success', 'Threshold berhasil direset ke pengaturan default');
    }

    public function destroy($pumpHouseId, $thresholdId)
    {
        $user = auth()->user();
        
        // Authorization check - perlu akses write
        if (!$user->isAdmin() && !$user->hasAccessToPumpHouse($pumpHouseId, 'write')) {
            abort(403, 'Anda tidak memiliki akses write ke rumah pompa ini.');
        }
        
        $threshold = PumpHouseThresholdSetting::where('pump_house_id', $pumpHouseId)
            ->findOrFail($thresholdId);
        
        $threshold->delete();

        return redirect()->route('admin.pump-house-thresholds.edit', $pumpHouseId)
            ->with('success', 'Threshold berhasil dihapus');
    }
}
