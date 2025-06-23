<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PumpHouse;
use App\Models\WaterLevelHistory;
use App\Models\Alert;
use App\Models\ThresholdSetting;
use App\Models\PumpHouseThresholdSetting;
use App\Services\AlertService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class WaterLevelController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Filter pump houses berdasarkan akses user
        $pumpHousesQuery = PumpHouse::with(['waterLevelHistory' => function($query) {
            $query->latest()->limit(1);
        }]);
        
        if (!$user->isAdmin()) {
            $accessibleIds = $user->getAccessiblePumpHouseIds();
            $pumpHousesQuery->whereIn('id', $accessibleIds);
        }
        
        $pumpHouses = $pumpHousesQuery->get();

        // Get sorting parameters
        $sortBy = $request->get('sort', 'recorded_at');
        $sortOrder = $request->get('order', 'desc');
        
        // Validate sort parameters
        $allowedSorts = ['recorded_at', 'water_level', 'pump_house_name'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'recorded_at';
        }
        
        $allowedOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $allowedOrders)) {
            $sortOrder = 'desc';
        }

        $query = WaterLevelHistory::with('pumpHouse');
        
        // Filter berdasarkan akses user
        if (!$user->isAdmin()) {
            $accessibleIds = $user->getAccessiblePumpHouseIds();
            $query->whereIn('pump_house_id', $accessibleIds);
        }
        
        // Apply sorting
        if ($sortBy === 'pump_house_name') {
            $query->join('pump_houses', 'water_level_histories.pump_house_id', '=', 'pump_houses.id')
                  ->orderBy('pump_houses.name', $sortOrder)
                  ->select('water_level_histories.*');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        $recentHistory = $query->paginate(20)->withQueryString();

        return Inertia::render('Admin/WaterLevel/Index', [
            'pumpHouses' => $pumpHouses,
            'recentHistory' => $recentHistory,
            'filters' => [
                'sort' => $sortBy,
                'order' => $sortOrder,
            ],
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        
        // Filter pump houses berdasarkan akses user (minimal write access)
        $pumpHousesQuery = PumpHouse::query();
        
        if (!$user->isAdmin()) {
            $accessibleIds = $user->getAccessiblePumpHouseIdsByLevel('write');
            $pumpHousesQuery->whereIn('id', $accessibleIds);
        }
        
        $pumpHouses = $pumpHousesQuery->get();

        return Inertia::render('Admin/WaterLevel/Create', [
            'pumpHouses' => $pumpHouses,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pump_house_id' => 'required|exists:pump_houses,id',
            'water_level' => 'required|numeric|min:0|max:10',
            'recorded_at' => 'nullable|date',
        ]);

        $user = auth()->user();
        
        // Cek akses write ke pump house
        if (!$user->isAdmin() && !$user->canWriteToPumpHouse($request->pump_house_id)) {
            abort(403, 'Anda tidak memiliki akses untuk menambah data ke rumah pompa ini.');
        }

        $recordedAt = $request->recorded_at ? Carbon::parse($request->recorded_at) : now();

        $waterLevel = WaterLevelHistory::create([
            'pump_house_id' => $request->pump_house_id,
            'water_level' => $request->water_level,
            'recorded_at' => $recordedAt,
        ]);

        // Update pump house last_updated timestamp only
        $pumpHouse = PumpHouse::find($request->pump_house_id);
        $pumpHouse->update([
            'last_updated' => $recordedAt,
        ]);

        // Check if alert needs to be created (only for current time records)
        if (!$request->recorded_at || Carbon::parse($request->recorded_at)->isToday()) {
            $this->checkAndCreateAlert($pumpHouse, $request->water_level);
        }

        return redirect()->route('admin.water-level.index')
            ->with('success', 'Data ketinggian air berhasil disimpan');
    }

    public function show($id)
    {
        $waterLevel = WaterLevelHistory::with('pumpHouse')->findOrFail($id);
        
        $user = auth()->user();
        
        // Cek akses read ke pump house
        if (!$user->isAdmin() && !$user->hasAccessToPumpHouse($waterLevel->pump_house_id)) {
            abort(403, 'Anda tidak memiliki akses untuk melihat data rumah pompa ini.');
        }

        // Get pump house specific thresholds
        $thresholds = PumpHouseThresholdSetting::where('pump_house_id', $waterLevel->pump_house_id)
            ->where('is_active', true)
            ->orderBy('water_level', 'asc')
            ->get();

        // Get global thresholds as fallback
        $globalThresholds = ThresholdSetting::where('is_active', true)
            ->orderBy('water_level', 'asc')
            ->get();

        return Inertia::render('Admin/WaterLevel/Show', [
            'waterLevel' => $waterLevel,
            'thresholds' => $thresholds,
            'globalThresholds' => $globalThresholds,
        ]);
    }

    public function edit($id)
    {
        $waterLevel = WaterLevelHistory::with('pumpHouse')->findOrFail($id);
        
        $user = auth()->user();
        
        // Cek akses write ke pump house
        if (!$user->isAdmin() && !$user->canWriteToPumpHouse($waterLevel->pump_house_id)) {
            abort(403, 'Anda tidak memiliki akses untuk mengedit data rumah pompa ini.');
        }
        
        // Filter pump houses berdasarkan akses user (minimal write access)
        $pumpHousesQuery = PumpHouse::query();
        
        if (!$user->isAdmin()) {
            $accessibleIds = $user->getAccessiblePumpHouseIdsByLevel('write');
            $pumpHousesQuery->whereIn('id', $accessibleIds);
        }
        
        $pumpHouses = $pumpHousesQuery->get();

        return Inertia::render('Admin/WaterLevel/Edit', [
            'waterLevel' => $waterLevel,
            'pumpHouses' => $pumpHouses,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'pump_house_id' => 'required|exists:pump_houses,id',
            'water_level' => 'required|numeric|min:0|max:10',
            'recorded_at' => 'required|date',
        ]);

        $waterLevel = WaterLevelHistory::findOrFail($id);
        
        $user = auth()->user();
        
        // Cek akses write ke pump house lama dan baru
        if (!$user->isAdmin()) {
            if (!$user->canWriteToPumpHouse($waterLevel->pump_house_id)) {
                abort(403, 'Anda tidak memiliki akses untuk mengedit data rumah pompa ini.');
            }
            if (!$user->canWriteToPumpHouse($request->pump_house_id)) {
                abort(403, 'Anda tidak memiliki akses untuk memindah data ke rumah pompa ini.');
            }
        }
        
        $waterLevel->update([
            'pump_house_id' => $request->pump_house_id,
            'water_level' => $request->water_level,
            'recorded_at' => Carbon::parse($request->recorded_at),
        ]);

        // Update pump house last_updated timestamp if this is the latest record
        $latestRecord = WaterLevelHistory::where('pump_house_id', $request->pump_house_id)
            ->latest('recorded_at')
            ->first();

        if ($latestRecord && $latestRecord->id === $waterLevel->id) {
            $pumpHouse = PumpHouse::find($request->pump_house_id);
            $pumpHouse->update([
                'last_updated' => $waterLevel->recorded_at,
            ]);
        }

        return redirect()->route('admin.water-level.index')
            ->with('success', 'Data ketinggian air berhasil diperbarui');
    }

    public function destroy($id)
    {
        $waterLevel = WaterLevelHistory::findOrFail($id);
        $pumpHouseId = $waterLevel->pump_house_id;
        
        $user = auth()->user();
        
        // Cek akses write ke pump house
        if (!$user->isAdmin() && !$user->canWriteToPumpHouse($pumpHouseId)) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus data rumah pompa ini.');
        }
        
        $waterLevel->delete();

        // Update pump house last_updated timestamp with the latest remaining record
        $latestRecord = WaterLevelHistory::where('pump_house_id', $pumpHouseId)
            ->latest('recorded_at')
            ->first();

        if ($latestRecord) {
            $pumpHouse = PumpHouse::find($pumpHouseId);
            $pumpHouse->update([
                'last_updated' => $latestRecord->recorded_at,
            ]);
        }

        return redirect()->route('admin.water-level.index')
            ->with('success', 'Data ketinggian air berhasil dihapus');
    }

    public function history($pumpHouseId, Request $request)
    {
        $pumpHouse = PumpHouse::findOrFail($pumpHouseId);
        
        $user = auth()->user();
        
        // Cek akses read ke pump house
        if (!$user->isAdmin() && !$user->hasAccessToPumpHouse($pumpHouseId)) {
            abort(403, 'Anda tidak memiliki akses untuk melihat riwayat rumah pompa ini.');
        }
        
        // Get sorting parameters
        $sortBy = $request->get('sort', 'recorded_at');
        $sortOrder = $request->get('order', 'desc');
        
        // Validate sort parameters
        $allowedSorts = ['recorded_at', 'water_level'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'recorded_at';
        }
        
        $allowedOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $allowedOrders)) {
            $sortOrder = 'desc';
        }
        
        $history = WaterLevelHistory::where('pump_house_id', $pumpHouseId)
            ->orderBy($sortBy, $sortOrder)
            ->paginate(50)
            ->withQueryString();

        // Get pump house specific thresholds
        $thresholds = PumpHouseThresholdSetting::where('pump_house_id', $pumpHouseId)
            ->where('is_active', true)
            ->orderBy('water_level', 'asc')
            ->get();

        // Get global thresholds as fallback
        $globalThresholds = ThresholdSetting::where('is_active', true)
            ->orderBy('water_level', 'asc')
            ->get();

        return Inertia::render('Admin/WaterLevel/History', [
            'pumpHouse' => $pumpHouse,
            'history' => $history,
            'thresholds' => $thresholds,
            'globalThresholds' => $globalThresholds,
            'filters' => [
                'sort' => $sortBy,
                'order' => $sortOrder,
            ],
        ]);
    }

    public function getChartData($pumpHouseId, Request $request)
    {
        $days = $request->get('days', 7);
        $startDate = Carbon::now()->subDays($days);

        $data = WaterLevelHistory::where('pump_house_id', $pumpHouseId)
            ->where('recorded_at', '>=', $startDate)
            ->orderBy('recorded_at')
            ->get()
            ->map(function ($item) {
                return [
                    'x' => $item->recorded_at->format('Y-m-d H:i'),
                    'y' => (float) $item->water_level,
                ];
            });

        return response()->json($data);
    }

    private function checkAndCreateAlert($pumpHouse, $waterLevel)
    {
        // Use AlertService to create contextualized water level alert
        $alertService = app(AlertService::class);
        
        // Get the highest exceeded threshold for this pump house
        $threshold = PumpHouseThresholdSetting::getExceededThresholdForPumpHouse($pumpHouse->id, $waterLevel);
        
        // Fallback to global threshold if no pump house specific threshold exists
        if (!$threshold) {
            $threshold = ThresholdSetting::getExceededThreshold($waterLevel);
        }
        
        if ($threshold && $threshold->name !== 'normal') {
            // Check if similar alert already exists in the last hour
            $existingAlert = Alert::where('type', 'water_level')
                ->where('pump_house_id', $pumpHouse->id)
                ->where('created_at', '>=', Carbon::now()->subHour())
                ->first();

            if (!$existingAlert) {
                // Create alert using AlertService for contextualized messaging
                $alertService->createWaterLevelAlert($pumpHouse, $waterLevel);
            }
        }
    }
} 