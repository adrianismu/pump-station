<?php

namespace App\Services;

use App\Models\PumpHouse;
use App\Models\WaterLevelHistory;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WaterLevelService
{
    /**
     * Record a single water level reading
     */
    public function recordWaterLevel(int $pumpHouseId, float $waterLevel, Carbon $recordedAt = null): WaterLevelHistory
    {
        return WaterLevelHistory::create([
            'pump_house_id' => $pumpHouseId,
            'water_level' => $waterLevel,
            'recorded_at' => $recordedAt ?? now(),
        ]);
    }

    /**
     * Get the latest water level for a pump house
     */
    public function getLatestWaterLevel(int $pumpHouseId): ?float
    {
        $latest = WaterLevelHistory::where('pump_house_id', $pumpHouseId)
            ->latest('recorded_at')
            ->first();

        return $latest ? (float) $latest->water_level : null;
    }

    /**
     * Get water level history for a pump house within a date range
     */
    public function getWaterLevelHistory(int $pumpHouseId, Carbon $startDate, Carbon $endDate): Collection
    {
        return WaterLevelHistory::where('pump_house_id', $pumpHouseId)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->orderBy('recorded_at', 'desc')
            ->get();
    }

    /**
     * Calculate average water level for a pump house within a date range
     */
    public function getAverageWaterLevel(int $pumpHouseId, Carbon $startDate, Carbon $endDate): float
    {
        $average = WaterLevelHistory::where('pump_house_id', $pumpHouseId)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->avg('water_level');

        return round((float) $average, 2);
    }

    /**
     * Check water level status against pump house thresholds
     */
    public function checkWaterLevelStatus(PumpHouse $pumpHouse, float $waterLevel): string
    {
        if ($pumpHouse->water_level_critical && $waterLevel >= $pumpHouse->water_level_critical) {
            return 'critical';
        }

        if ($pumpHouse->water_level_warning && $waterLevel >= $pumpHouse->water_level_warning) {
            return 'warning';
        }

        return 'normal';
    }

    /**
     * Bulk record water levels
     */
    public function bulkRecordWaterLevels(int $pumpHouseId, array $records): bool
    {
        try {
            $data = collect($records)->map(function ($record) use ($pumpHouseId) {
                return [
                    'pump_house_id' => $pumpHouseId,
                    'water_level' => $record['water_level'],
                    'recorded_at' => Carbon::parse($record['recorded_at']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            });

            WaterLevelHistory::insert($data->toArray());
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get water level statistics for a pump house
     */
    public function getWaterLevelStatistics(int $pumpHouseId, Carbon $startDate = null, Carbon $endDate = null): array
    {
        $query = WaterLevelHistory::where('pump_house_id', $pumpHouseId);

        if ($startDate && $endDate) {
            $query->whereBetween('recorded_at', [$startDate, $endDate]);
        }

        $records = $query->get();

        if ($records->isEmpty()) {
            return [
                'current_water_level' => null,
                'average_water_level' => 0,
                'min_water_level' => null,
                'max_water_level' => null,
                'total_readings' => 0,
                'trend' => 'stable'
            ];
        }

        $latest = $this->getLatestWaterLevel($pumpHouseId);
        $waterLevels = $records->pluck('water_level');

        return [
            'current_water_level' => $latest,
            'average_water_level' => round($waterLevels->avg(), 2),
            'min_water_level' => $waterLevels->min(),
            'max_water_level' => $waterLevels->max(),
            'total_readings' => $records->count(),
            'trend' => $this->calculateTrend($records)
        ];
    }

    /**
     * Calculate water level trend
     */
    private function calculateTrend(Collection $records): string
    {
        if ($records->count() < 2) {
            return 'stable';
        }

        $sortedRecords = $records->sortBy('recorded_at');
        $first = $sortedRecords->first()->water_level;
        $last = $sortedRecords->last()->water_level;

        $difference = $last - $first;
        $threshold = 0.1; // 10cm threshold

        if ($difference > $threshold) {
            return 'increasing';
        } elseif ($difference < -$threshold) {
            return 'decreasing';
        }

        return 'stable';
    }

    /**
     * Get pump houses with current water level status
     */
    public function getPumpHousesWithCurrentStatus(): Collection
    {
        return PumpHouse::with(['waterLevelHistory' => function ($query) {
            $query->latest('recorded_at')->limit(1);
        }])->get()->map(function ($pumpHouse) {
            $currentLevel = $pumpHouse->getCurrentWaterLevel();
            
            return [
                'id' => $pumpHouse->id,
                'name' => $pumpHouse->name,
                'current_water_level' => $currentLevel,
                'status' => $this->checkWaterLevelStatus($pumpHouse, $currentLevel ?: 0),
                'last_updated' => $pumpHouse->waterLevelHistory->first()?->recorded_at,
            ];
        });
    }

    /**
     * Validate water level reading
     */
    public function validateWaterLevel(float $waterLevel): bool
    {
        return $waterLevel >= 0 && $waterLevel <= 10; // Reasonable range for water levels
    }

    /**
     * Get hourly averages for a given day
     */
    public function getHourlyAverages(int $pumpHouseId, Carbon $date): array
    {
        $startDate = $date->copy()->startOfDay();
        $endDate = $date->copy()->endOfDay();

        $records = WaterLevelHistory::where('pump_house_id', $pumpHouseId)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($record) {
                return $record->recorded_at->format('H');
            });

        $hourlyAverages = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourKey = sprintf('%02d', $hour);
            $hourRecords = $records->get($hourKey, collect());
            
            $hourlyAverages[] = [
                'hour' => $hour,
                'average_level' => $hourRecords->isEmpty() ? null : round($hourRecords->avg('water_level'), 2),
                'reading_count' => $hourRecords->count()
            ];
        }

        return $hourlyAverages;
    }
} 